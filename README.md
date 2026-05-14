# XFY Loader

一个基于 Express + PHP 的服务端加载页系统，为单页应用提供平滑的加载过渡体验。

## 架构概览

```
客户端请求 → Express 服务器
                │
                ├─ ─ 第一层：.php 文件 → 直接执行 PHP
                │
                ├─ ─ 第二层：.html 文件 → 检查同路径 .function 文件
                │       ├─ 存在 → 执行 Loader.php，返回加载页
                │       └─ 不存在 → 交给下一层
                │
                └─ ─ 第三层：静态文件服务
```

## 功能特点

- **服务端加载页**：通过 PHP 在服务端渲染加载页，注入页面所需的 JS/CSS 资源
- **自动路由拦截**：访问 `example.html` 时自动寻找同路径的 `example.function`，无侵入式集成
- **3D 加载动画**：美观的 CSS 3D 立方体加载动画
- **平滑过渡**：加载完成后自动淡出加载器、淡入应用内容
- **安全隔离**：`Loader.php` 存放在 `public/` 之外，无法被直接 HTTP 访问
- **自动脚本生成**：提供 Python 工具自动解析构建产物生成 `.function` 文件

## 快速开始

### 安装依赖

```bash
cd XFYLoader
npm install
```

### 启动服务器

```bash
npm start
```

服务器默认在 `http://localhost:3009` 启动。

### 访问应用

将构建好的单页应用（如 Vite 打包产物）放入 `public/` 目录下，例如：

```
public/
  example-page/
    index.html
    assets/
      index-xxx.js
      index-xxx.css
```

使用工具生成 `.function` 文件：

```bash
python tools/parse_html.py public/example-page/index.html
```

生成的输出写入到 `public/example-page/index.function`，之后访问：

```
http://localhost:3009/example-page/index.html
```

即可看到加载页动画，待资源加载完成后自动显示应用内容。

## 文件说明

### 核心文件

| 文件 | 位置 | 说明 |
|------|------|------|
| [server.js](server.js) | 项目根目录 | Express 服务器，3 层中间件处理请求 |
| [Loader.php](Loader.php) | 项目根目录 | PHP 加载页，渲染动画 + 注入资源，放在 public 外防直接访问 |
| [tools/parse_html.py](tools/parse_html.py) | tools/ | 解析 HTML 构建产物，自动生成 .function 文件 |

### .function 文件格式

`.function` 文件是描述页面资源的配置文件，使用 XML 风格标签：

```xml
<title>页面标题</title>
<icon>favicon.svg</icon>
<mount>root</mount>
<js>assets/index-xxx.js</js>
<css>assets/index-xxx.css</css>
```

| 标签 | 说明 | 必填 |
|------|------|------|
| `<title>` | 页面标题 | 是 |
| `<icon>` | 网站图标路径 | 否 |
| `<mount>` | 挂载点 ID（`root` 或 `app`） | 是（默认 `root`） |
| `<js>` | JavaScript 文件路径（可多个） | 否 |
| `<css>` | CSS 文件路径（可多个） | 否 |

### 工具：parse_html.py

自动解析 Vite / webpack 等构建产物的 `index.html`，提取并输出 `.function` 文件内容。

```bash
# 基本用法（自动寻找 public 目录）
python tools/parse_html.py public/example-page/index.html

# 指定 public 目录
python tools/parse_html.py path/to/index.html --public-dir public
```

## 工作流程

1. 用户访问 `http://localhost:3009/example-page/index.html`
2. 服务器检测到 `.html` 请求，查找 `public/example-page/index.function`
3. 找到后执行 `Loader.php`，传入 `.function` 文件路径
4. Loader.php 解析 `.function` 文件，提取标题、图标、JS、CSS 等信息
5. 返回完整 HTML：包含 3D 加载动画 + `<link>` 预加载 CSS + `<script>` 加载 JS
6. 浏览器加载完成后，页面 JS 自动淡出加载器、展示挂载点中的应用内容

## 目录结构

```
XFYLoader/
├── Loader.php            # PHP 加载页（安全隔离，不可直接访问）
├── server.js             # Express 服务器入口
├── package.json
├── tools/
│   └── parse_html.py     # HTML 解析工具
└── public/
    ├── index.html         # 欢迎页（可选）
    └── example-page/      # 示例应用
        ├── index.html     # 构建产物
        ├── index.function # 加载页配置文件
        └── assets/        # 静态资源
```

## 技术栈

- **Node.js / Express** — Web 服务器
- **PHP** — 服务端加载页渲染
- **CSS 动画** — 3D 立方体加载动画
- **Python** — HTML 解析工具
