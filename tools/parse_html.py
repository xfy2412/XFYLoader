#!/usr/bin/env python3
"""
解析 HTML 文件，提取资源信息并生成 .function 文件内容。

用法：
    python tools/parse_html.py public/example-page/index.html
    python tools/parse_html.py path/to/index.html --public-dir public
    python tools/parse_html.py path/to/index.html --spa
"""

import sys
import os
from html.parser import HTMLParser
from urllib.parse import urlparse


class Parser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.title = ''
        self.icon = ''
        self.mount = ''
        self.js_files = []
        self.css_files = []
        self._in_title = False
        self._title_text = ''

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)

        if tag == 'title':
            self._in_title = True
            self._title_text = ''
            return

        if tag == 'base':
            return

        if tag == 'link' and a.get('rel') == 'icon':
            href = a.get('href', '')
            if href and not self.icon:
                self.icon = href
            return

        if tag == 'link' and a.get('rel') == 'stylesheet':
            href = a.get('href', '')
            if href:
                self.css_files.append(href)
            return

        if tag == 'script' and a.get('src', ''):
            self.js_files.append(a['src'])
            return

        if tag == 'div' and a.get('id') in ('root', 'app'):
            self.mount = a['id']

    def handle_endtag(self, tag):
        if tag == 'title' and self._in_title:
            self._in_title = False
            self.title = self._title_text.strip()

    def handle_data(self, data):
        if self._in_title:
            self._title_text += data


def find_public_dir(html_path):
    abs_path = os.path.abspath(html_path)
    parts = abs_path.replace('\\', '/').split('/')
    for i, part in enumerate(parts):
        if part == 'public' and i < len(parts) - 1:
            return '/'.join(parts[:i + 1])
    return os.path.dirname(os.path.dirname(abs_path))


def resolve(html_dir, public_dir, paths):
    def to_relative(p):
        if p.startswith(('http://', 'https://', '//')):
            return p
        if p.startswith('/'):
            rel = os.path.relpath(html_dir, public_dir)
            return os.path.normpath(os.path.join(rel, p.lstrip('/'))).replace('\\', '/')
        abs_p = os.path.normpath(os.path.join(html_dir, p))
        return os.path.relpath(abs_p, public_dir).replace('\\', '/')

    if isinstance(paths, str):
        return to_relative(paths)
    return [to_relative(p) for p in paths]


def main():
    if len(sys.argv) < 2 or sys.argv[1] in ('-h', '--help'):
        print(f'用法：{sys.argv[0]} <path/to/index.html> [--public-dir DIR]')
        sys.exit(0)

    html_path = os.path.abspath(sys.argv[1])
    if not os.path.isfile(html_path):
        print(f'错误：文件未找到：{html_path}', file=sys.stderr)
        sys.exit(1)

    public_dir = None
    is_spa = False
    for i, arg in enumerate(sys.argv):
        if arg == '--public-dir' and i + 1 < len(sys.argv):
            public_dir = os.path.abspath(sys.argv[i + 1])
        elif arg == '--spa':
            is_spa = True

    if not public_dir:
        public_dir = find_public_dir(html_path)

    html_dir = os.path.dirname(html_path)

    with open(html_path, 'r', encoding='utf-8') as f:
        content = f.read()

    parser = Parser()
    parser.feed(content)

    icon = resolve(html_dir, public_dir, parser.icon) if parser.icon else ''
    js_files = resolve(html_dir, public_dir, parser.js_files) if parser.js_files else []
    css_files = resolve(html_dir, public_dir, parser.css_files) if parser.css_files else []

    if is_spa:
        print('<spa>true</spa>')
    print(f'<title>{parser.title}</title>')
    if icon:
        print(f'<icon>{icon}</icon>')
    if parser.mount:
        print(f'<mount>{parser.mount}</mount>')
    for js in js_files:
        print(f'<js>{js}</js>')
    for css in css_files:
        print(f'<css>{css}</css>')


if __name__ == '__main__':
    main()