const { execSync } = require('child_process');

try {
    // Check if docker is running by executing 'docker info'
    execSync('docker info', { stdio: 'ignore' });
    console.log('Docker daemon is running. Starting Soketi WebSocket server...');
    execSync('docker compose -f docker-compose.soketi.yml up -d', { stdio: 'inherit' });
} catch (error) {
    console.warn('\x1b[33m%s\x1b[0m', '\n======================================================================');
    console.warn('\x1b[33m%s\x1b[0m', ' WARNING: Docker daemon is not running!');
    console.warn('\x1b[33m%s\x1b[0m', ' Please make sure Docker Desktop is open.');
    console.warn('\x1b[33m%s\x1b[0m', ' Soketi WebSocket server cannot be started.');
    console.warn('\x1b[33m%s\x1b[0m', ' Development server and webhook tunnel will continue to launch.');
    console.warn('\x1b[33m%s\x1b[0m', '======================================================================\n');
}
