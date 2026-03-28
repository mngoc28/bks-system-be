#!/usr/bin/env node
import 'source-map-support/register';
import * as cdk from 'aws-cdk-lib';
import { CdkStack } from '../lib/cdk-stack';


const app = new cdk.App();

/**
 * Cdk stack for staging env 
 * contextStaging env context in cdk.json
 * stagingAccount to get account, region to deploy
 */
 const inputContext = app.node.tryGetContext('contxt')
 const globalContext = app.node.tryGetContext('global')
 const context = app.node.tryGetContext(inputContext)
 const env = context['env']

new CdkStack(app, "UserApiStack", context, {
  stackName: `${globalContext.prefix}-${env.environment}-UserApiStack`,
  description: "Create userapi stack",
  env: { 
    account: `${env.account}`, 
    region: `${env.region}` 
  }
  /* For more information, see https://docs.aws.amazon.com/cdk/latest/guide/environments.html */
});

app.synth();