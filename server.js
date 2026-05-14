const express = require('express');
const path = require('path');
const fs = require('fs');
const { execFile } = require('child_process');

const app = express();
const PORT = 3009;

const publicDir = path.join(__dirname, 'public');

// 第一层：处理 .php 文件请求
app.use((req, res, next) => {
  if (!req.path.endsWith('.php')) return next();

  const filePath = path.join(publicDir, req.path);
  if (!fs.existsSync(filePath)) return next();

  execFile('php', [filePath], { timeout: 10000 }, (error, stdout, stderr) => {
    if (error) {
      console.error('PHP 执行错误：', stderr);
      return next(error);
    }
    res.type('html').send(stdout);
  });
});

// 第二层：处理 .html 和目录请求，检查是否存在同路径同名的 .function 文件
// 存在时返回加载页，不存在时交给下一层处理
app.use((req, res, next) => {
  let functionRelPath;

  if (req.path.endsWith('.html')) {
    functionRelPath = req.path.replace(/\.html$/, '.function');
  } else if (req.path.endsWith('/')) {
    functionRelPath = req.path + 'index.function';
  } else {
    return next();
  }

  const functionFullPath = path.join(publicDir, functionRelPath);

  if (!fs.existsSync(functionFullPath)) return next();

  const loaderPath = path.join(__dirname, 'Loader.php');
  execFile('php', [loaderPath, functionRelPath], { timeout: 10000 }, (error, stdout, stderr) => {
    if (error) {
      console.error('PHP 执行错误：', stderr);
      return next(error);
    }
    res.type('html').send(stdout);
  });
});

// 第三层：提供静态文件服务
app.use(express.static(publicDir));

app.listen(PORT, () => {
  console.log(`服务器已启动，访问地址：http://localhost:${PORT}`);
});
