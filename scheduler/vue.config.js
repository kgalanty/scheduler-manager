module.exports = {
  publicPath: ".",
 //devServer: {
   // https: {
   //   key: fs.readFileSync('./certs/example.com+5-key.pem'),
   //   cert: fs.readFileSync('./certs/example.com+5.pem'),
   // },
   //  public: 'https://localhost:8080/',
   //  disableHostCheck: true,
   // disableHostCheck: true,
   devServer: {
     // open: process.platform === 'darwin',
     // host: '0.0.0.0',
     // port: 8080, // CHANGE YOUR PORT HERE!
     // https: true,
     // hotOnly: false,
     //public: 'https://localhost:8080/',
      
   proxy: {
     "^/scheduleapi": {
       target: "https://my.tmdhosting.com/schedule",
       logLevel: "debug",
       //changeOrigin: true,
      // secure: true,
       withCredentials: true,
       //pathRewrite: { "^/scheduleapi": "/scheduleapi" }
     }
   }
 }}