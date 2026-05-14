const express = require('express');
const path = require('path');
const fs = require('fs');
const { execFile } = require('child_process');

const app = express();
const PORT = 3009;

const publicDir = path.join(__dirname, 'public');

// 启动时扫描所有 index.function，构建 SPA 前缀缓存
const spaPrefixes = {}; // { '/example-page/': 'example-page/index.function' }

function scanFunctionFiles(dir, relativeDir) {
  let entries;
  try {
    entries = fs.readdirSync(dir, { withFileTypes: true });
  } catch {
    return;
  }
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      scanFunctionFiles(fullPath, relativeDir + entry.name + '/');
    } else if (entry.name === 'index.function') {
      const content = fs.readFileSync(fullPath, 'utf-8');
      if (/<spa>\s*true\s*<\/spa>/i.test(content)) {
        const prefixDir = '/' + relativeDir.replace(/\\/g, '/');
        const functionPath = (relativeDir + entry.name).replace(/\\/g, '/');
        spaPrefixes[prefixDir] = functionPath;
      }
    }
  }
}

scanFunctionFiles(publicDir, '');

// 查找匹配的最长 SPA 前缀
function matchSpaPrefix(requestPath) {
  const sortedPrefixes = Object.keys(spaPrefixes).sort((a, b) => b.length - a.length);
  const pathToCheck = requestPath.endsWith('/') ? requestPath : requestPath + '/';
  for (const prefix of sortedPrefixes) {
    if (pathToCheck === prefix || pathToCheck.startsWith(prefix)) {
      return spaPrefixes[prefix];
    }
  }
  return null;
}

// 通用加载页渲染函数
function serveLoader(res, next, functionRelPath) {
  const loaderPath = path.join(__dirname, 'Loader.php');
  execFile('php', [loaderPath, functionRelPath], { timeout: 10000 }, (error, stdout, stderr) => {
    if (error) {
      console.error('PHP 执行错误：', stderr);
      return next(error);
    }
    res.type('html').send(stdout);
  });
}

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

// 第二层：拦截 .function 文件，禁止直接 HTTP 访问
app.use((req, res, next) => {
  if (req.path.endsWith('.function')) {
    return res.status(404).type('text').send('Not Found');
  }
  next();
});

// 第三层：处理 .html 和目录请求，检查是否存在同路径同名的 .function 文件
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

  serveLoader(res, next, functionRelPath);
});

// 第四层：SPA 路由回退——处理无扩展名的路径，匹配 SPA 前缀时返回加载页
app.use((req, res, next) => {
  const lastSegment = req.path.split('/').filter(Boolean).pop() || '';
  if (lastSegment.includes('.')) return next();

  const functionRelPath = matchSpaPrefix(req.path);
  if (!functionRelPath) return next();

  serveLoader(res, next, functionRelPath);
});

// 第五层：提供静态文件服务
app.use(express.static(publicDir));

app.listen(PORT, () => {
  const prefixes = Object.keys(spaPrefixes);
  if (prefixes.length > 0) {
    console.log(`SPA 前缀已注册：${prefixes.join(', ')}`);
  }
  console.log(`服务器已启动，访问地址：http://localhost:${PORT}`);
});
