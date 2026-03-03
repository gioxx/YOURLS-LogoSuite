# ⭐ YOURLS Logo Suite

Customize YOURLS branding from a single admin page: logo, title, and display behavior.

[![Latest Release](https://img.shields.io/github/v/release/gioxx/YOURLS-LogoSuite)](https://github.com/gioxx/YOURLS-LogoSuite/releases)
[![License](https://img.shields.io/github/license/gioxx/YOURLS-LogoSuite)](LICENSE)

## 🚀 Features

- Admin menu entry: `Branding Settings`
- Custom logo via URL (`PNG`, `JPG`, `SVG`, etc.)
- Local logo upload (`PNG`, `JPG/JPEG`, `GIF`, `WEBP`, max 5 MB)
- Live logo preview with load/error feedback
- Logo display controls:
  - `Width (px)`
  - `Height (px)`
  - `Keep aspect ratio`
- Custom browser title for YOURLS admin pages
- Optional `"(YOURLS)"` suffix in title
- HTTPS safety check: warns/blocks insecure `http://` logo URL when admin runs on HTTPS
- Update-available notice + badge based on GitHub latest release
- i18n-ready (`English`, `Italian`)

## 🛠️ Installation

1. Download the latest release: <https://github.com/gioxx/YOURLS-LogoSuite/releases>
2. Unzip into `user/plugins/yourls-logo-suite/`
3. Activate the plugin from YOURLS admin
4. Open `Manage Plugins` → `Branding Settings`

Requires YOURLS `1.9+`.

## ⚙️ Usage

### Logo

- Set an image URL, or upload a local file
- Uploaded files are stored in `user/uploads/logo-suite/`
- If you select a file, click `Save Settings` to complete upload

### Logo Size

- Use `Width` / `Height` to control rendered logo size
- Keep `Keep aspect ratio` enabled for proportional resize
- Leave width/height empty to use default rendering behavior

### Page Title

- Set a custom admin page title
- Choose whether to keep the `"(YOURLS)"` suffix

### Reset

- `Reset to Default` clears all saved options (logo, title, size settings)

## 🌐 Translation

Available languages:

- 🇬🇧 English (default)
- 🇮🇹 Italian

Translations live in `languages/` (`.po` / `.mo`).

## 📝 Changelog

Release notes: <https://github.com/gioxx/YOURLS-LogoSuite/releases>

## 📄 License

MIT License. See [LICENSE](LICENSE).

## 🤝 Contributing

Issues and PRs are welcome: <https://github.com/gioxx/YOURLS-LogoSuite/issues>
