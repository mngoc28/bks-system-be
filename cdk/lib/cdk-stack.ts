import { Stack, StackProps,
  aws_ecs as ecs,
  aws_ec2 as ec2,
  aws_route53 as route53,
  aws_ecr as ecr,
  aws_ecs_patterns as ecs_patterns,
  aws_elasticloadbalancingv2 as alb,
  aws_rds as rds,
  aws_certificatemanager as acm,
  aws_secretsmanager as secretsmanager,
  aws_route53_targets as targets,
  aws_codepipeline as codepipeline,
  aws_codepipeline_actions as actions,
  aws_codebuild as codebuild,
  aws_iam as iam,
  aws_logs as logs, 
  aws_applicationautoscaling as appscaling,
  RemovalPolicy} from 'aws-cdk-lib';
import * as cdk from 'aws-cdk-lib';
import { SslPolicy } from 'aws-cdk-lib/aws-elasticloadbalancingv2'
import { Construct } from 'constructs';
import { TargetType } from 'aws-cdk-lib/aws-elasticloadbalancingv2';
import { LogGroup } from 'aws-cdk-lib/aws-logs';
import { AwsLogDriver } from 'aws-cdk-lib/aws-ecs';
export class CdkStack extends Stack {
  constructor(scope: Construct, id: string, context: any, props?: StackProps) {
    super(scope, id, props);

    const globalContext = this.node.tryGetContext('global')

    const env = context['env']
    /**
     * ########## Get vpc context ##################
     */
     const vpcContext = context['vpc']

     /**
      * ########## Get rds context ##################
      */
     const rdsContext = context['rds']
 
     /**
      * ########## Get secretmanger context ##################
      */
     const secretManagerContext = context['secretmanager']
 
     /**
      * ########## Get route53 context ##################
      */
     const route53Context = context['route53']
 
     /**
      * ########## Get ecs context ##################
      */
     const ecsContext = context['ecs']

     /**
      * ########## Get ecs context ##################
      */
      const ecrContext = context['ecr']
     
     /**
      * ########## Get fargate context ##################
      */
     const fargateContext = context['fargate']
 
     /**
      * ########## Get log context ##################
      */
     const logsContext = context['log']
 
     /**
      * ########## Import existing vpc ##################
      */
     const vpc = ec2.Vpc.fromLookup(this, 'ImportedVpc', {
       vpcId: `${vpcContext.vpcId}`
     })
 
     /**
      * ########## Import Ecs cluster ##################
      */
     const cluster = ecs.Cluster.fromClusterAttributes(this, 'ImportedEcs', {
       vpc: vpc,
       clusterArn: `${ecsContext.arn}`,
       clusterName: `${ecsContext.clusterName}`,
       securityGroups: [],
     })
 
     /**
      * ########## Import domain ##################
      */
     const domainZone = route53.HostedZone.fromLookup(this, 'Zone', {
       domainName: `${route53Context.domainName}`
     })
 
     /**
      * ########## Import ECR repo ##################
      */
     const imageRepo = ecr.Repository.fromRepositoryName(this, 'ImportedEcr', `${ecrContext.name}`)
     
     /**
      * ########## Create, get image from ecr with tag equal environment IMAGE_TAG ##################
      */
     const tag = (process.env.IMAGE_TAG) ? process.env.IMAGE_TAG : 'latest';
     const image = ecs.ContainerImage.fromEcrRepository(imageRepo, tag);
 
     /**
      * ########## Import acm for HTTPS ##################
      */
     const certificate = acm.Certificate.fromCertificateArn(this, 'Cert', `${fargateContext.certificateArn}`);
 
     /**
      * ########## Import secretmanager to get database secret ##################
      */
     const rdsSecretManager = secretsmanager.Secret.fromSecretAttributes(this, 'ImportedSecret',{
       secretCompleteArn: `${secretManagerContext.arn}`
     });
     
     /**
      * ########## Import database to get database secret ##################
      */
     const db = rds.DatabaseCluster.fromDatabaseClusterAttributes(this, "ImportedRds", {
       clusterIdentifier: `${rdsContext.arn}`,
     })
     const userApiLogGroup = new logs.LogGroup(this, "UserApiLogGroup", {
      logGroupName: `${logsContext.name}`,
      retention: logs.RetentionDays.THREE_MONTHS
     });

     if (logsContext.removalPolicy === "retain"){
      userApiLogGroup.applyRemovalPolicy(RemovalPolicy.RETAIN);
     }else if (logsContext.removalPolicy === "destroy"){
      userApiLogGroup.applyRemovalPolicy(RemovalPolicy.DESTROY);
     }else{
      console.error("[ERROR] Removal Policy value is retain|destroy only");
     }

     const userApiLogDriver = new ecs.AwsLogDriver({
      logGroup: userApiLogGroup,
      streamPrefix: `${logsContext.streamPrefix}`
     })
     
     /**
      * ########## Create ECS container ##################
      * Application Loadbalancer
      * Task Definitions
      */
     const ecs_pattern = new ecs_patterns.ApplicationLoadBalancedFargateService(this, 'FargateService',{
      cluster: cluster,
      memoryLimitMiB: parseInt(`${fargateContext.memory}`),
      cpu: parseInt(`${fargateContext.cpu}`),
      desiredCount: parseInt(`${fargateContext.desiredCount}`),
      certificate,
      sslPolicy: SslPolicy.RECOMMENDED,
      domainName: `${fargateContext.domainName}`,
      domainZone: domainZone,
      redirectHTTP: true,
      taskImageOptions: {
        image: image, // image that task definitions using
        secrets: {
          DB_CONNECTION: ecs.Secret.fromSecretsManager(rdsSecretManager, 'engine'),
          DB_HOST: ecs.Secret.fromSecretsManager(rdsSecretManager, 'host'),
          DB_PORT: ecs.Secret.fromSecretsManager(rdsSecretManager, 'port'),
          DB_USERNAME: ecs.Secret.fromSecretsManager(rdsSecretManager, 'username'),
          DB_PASSWORD: ecs.Secret.fromSecretsManager(rdsSecretManager, 'password'),
        },
        environment: {
          DB_DATABASE: `${rdsContext.databaseName}`
        },
        containerName: 'userapi', // name of container 
        containerPort: 80, // port of container listen
        logDriver: userApiLogDriver
      },
      publicLoadBalancer: true, // internet-facing
      loadBalancerName: `${globalContext.prefix}-${env.environment}-userapi-alb`, // name of loadbalancer
      assignPublicIp: false, // private subnets
      securityGroups: [] 
    })    
    
    /**
     * ########### Auto scalling group #################
     */

    const autoScaling = ecs_pattern.service.autoScaleTaskCount({
      minCapacity: 2,
      maxCapacity: 10,
    })
    
    autoScaling.scaleOnCpuUtilization('CpuScaling', {
      targetUtilizationPercent: 60,
    })
    autoScaling.scaleOnMemoryUtilization('MemoryScaling', {
      targetUtilizationPercent: 60,
    })
     /**
      * ########## RDS allow connection from ecs container ##################
      */
     db.connections.allowFrom(ecs_pattern.loadBalancer, ec2.Port.tcp(3306))


    /**
      * ########## Cronjob ##################
      */
    new ecs_patterns.ScheduledFargateTask(this, 'ScheduledFargateTask', {
      cluster,
      desiredTaskCount: 1,
      enabled: true,
      scheduledFargateTaskImageOptions: {
          image,
          command: ['php', 'artisan', 'schedule:run'],
          cpu: parseInt(`${fargateContext.cpu}`),
          memoryLimitMiB: parseInt(`${fargateContext.memory}`),
          secrets: {
            DB_CONNECTION: ecs.Secret.fromSecretsManager(rdsSecretManager, 'engine'),
            DB_HOST: ecs.Secret.fromSecretsManager(rdsSecretManager, 'host'),
            DB_PORT: ecs.Secret.fromSecretsManager(rdsSecretManager, 'port'),
            DB_USERNAME: ecs.Secret.fromSecretsManager(rdsSecretManager, 'username'),
            DB_PASSWORD: ecs.Secret.fromSecretsManager(rdsSecretManager, 'password'),
          },
          environment: {
            DB_DATABASE: `${rdsContext.databaseName}`
          },
          logDriver: userApiLogDriver
      },
      ruleName: `${globalContext.prefix}-${env.environment}-userapi-cronjob-rule`,
      schedule: appscaling.Schedule.expression('rate(1 minute)'),
      platformVersion: ecs.FargatePlatformVersion.LATEST
    });
  }
}
