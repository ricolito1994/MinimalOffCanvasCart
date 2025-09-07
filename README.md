<h1>Minimal Offcanvas Cart Plugin</h1>

<p>A shopware 6 plugin for minimal off canvas cart for newly added items and cross selling items.</p>

<h3>SETUP</h3>

<p>Assuming you have setup shopware 6 app dockerized in your local.</p>

<ol>
  <li>Create a plugin: bin/console plugin:create MinimalOffCanvasCart</li>
  <li>Activate and install: bin/console plugin:install --activate MinimalOffCanvasCart</li>
  <li>Refresh the plugin: bin/console plugin:refresh</li>
  <li>Changes in .xml files mainly services.xml: bin/console plugin:update MinimalOffCanvasCart</li>
  <li>If changes are in frontend: bin/build-storefront.sh</li>
  <li>To run should be in root dir: /var/www/html </li>
</ol>
