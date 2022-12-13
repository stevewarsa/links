const { createProxyMiddleware } = require('http-proxy-middleware');

module.exports = (app) => {
    app.use(
        createProxyMiddleware('/links-app', {
            target: 'http://localhost:8080',
            changeOrigin: true,
            logLevel: "debug",
            headers: {
                Connection: "keep-alive"
            }
        })
    );
}