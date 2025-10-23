# People & Pixel – Marketing website

This directory contains a simple static marketing site for People & Pixel.

Structure
- index.html — landing page with product overview and CTAs
- features.html — list of implemented and upcoming features
- screenshots.html — gallery of UI screenshots (uses SVG placeholders by default)
- contact.html — contact information and a simple mailto form stub
- impressum.html — German Impressum (legal notice) template (placeholders)
- css/styles.css — minimal, responsive styling (no build tooling)
- img/ — images and SVG placeholders (replace with real screenshots)

How to preview locally
1. Any static file server will do. Examples:
   - Python: `python -m http.server 8081` (then open http://localhost:8081/website/)
   - PHP built-in: `php -S localhost:8081 -t .` (then open http://localhost:8081/website/)
2. Or open the HTML files directly in your browser (double‑click). Some features like relative links still work.

How to deploy
- GitHub Pages: Point Pages to the repository root or a `gh-pages` branch that contains this `website/` folder. Use Pages settings to set `/website` as the base path if serving from root.
- Netlify/Vercel: Drag‑and‑drop the `website/` folder or configure a project with `Publish directory = website`.
- Nginx/Apache: Serve the `website/` directory as a static site on your domain (e.g., marketing subdomain).

Screenshots
- Replace files in `website/img/` with real screenshots exported at ~1600×900.
- Update references in `screenshots.html` or keep the same file names.

Linking from the app
- You can add a link from the in‑app footer or header to `/website/` if both are served under the same host. This is optional.

Notes
- The site is framework‑free (no build step) and aims to be easily portable.
- Placeholders contain dummy company/contact data; replace for production.
