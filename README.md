<h1>Minimal Offcanvas Cart Plugin</h1>

<p>A shopware 6 plugin for minimal off canvas cart for newly added items and cross selling items.</p>

<h3>Setup SFTP to your IDE</h3>
<p>for this we are using VSCODE</p>
<ul>
  <li>download extension called SFTP by Natizyskunk</li>
  <li>create .vscode folder/directory inside your project root</li>
  <li>create a file called sftp.json</li>
  <li>
    <pre>
      { 
          "name": "shopware", 
          "host": "localhost", 
          "port": 22, 
          "username": "dockware", 
          "password": "dockware", 
          "remotePath": "/var/www/html", 
          "context": "c:/(yourfoldername)/src", 
          "uploadOnSave": true, 
          "syncOption": { 
              "delete": true 
          } 
      }
    </pre>
  </li>
  <li>
    <code>ctrl + shift + p</code> to your .vscode then search sftp
  </li>
  <li>Your code/changes should be uploaded to your shopware container.</li>
  <li>
      from your shopware container to your shopware local folder: <code>docker cp shopware:/var/www/html/. ./src</code>
      <p>for example: if you create a migration which generates a file on your shopware container.</p>
  </li>
</ul>

<h3>SETUP</h3>

<p>Assuming you have setup shopware 6 app dockerized in your local.</p>

<ol>
  <li>Create a plugin: bin/console plugin:create MinimalOffCanvasCart</li>
  <li>Activate and install: bin/console plugin:install --activate MinimalOffCanvasCart</li>
  <li>Refresh the plugin: bin/console plugin:refresh</li>
  <li>To copy changes from shopware container to your project folder -> <code>docker cp shopware:/var/www/html/. ./src</code></li>
  <li>Changes in .xml files mainly services.xml: bin/console plugin:update MinimalOffCanvasCart</li>
  <li>If changes are in frontend: bin/build-storefront.sh</li>
  <li>To run should be in root dir: /var/www/html </li>
</ol>

<h2>CROSS SELLING</h2>

<ol>
  <li>To add cross selling visit: http://localhost/admin</li>
  <li>Create user first: bin/console user:create -a -p <password> --firstName <firstname> --lastName <lastname> --email <email> <username></li>
  <li>Catalouges -> Products -> select a product (...) -> Cross Selling tab -> save</li>
</ol>

<h2>CROSS SELLING CUSTOM FIELD</h2>

<p>By running bin/console plugin:update/install - will automatically populate custom fields for cross selling.</p>

<h2>MIGRATIONS</h2>
<ul>
  <li>- Create Migration : <code>./bin/console database:create-migration -p (PluginName) --name (MigrationName)</code></li>
  <li>- Run Migration : <code>./bin/console database:migrate (PluginName) --all</code></li>
</ul>

<h2>WATCH/HOT RELOAD STORE FRONT CHANGES</h2>
<code>./bin/watch-storefront.sh</code>

<h2>Override Templates</h2>
<ul>
  <li>Product Price: <code>/storefront/component/product/card/price-unit.html.twig</code></li>
  <li>Header Layout: <code>/storefront/layout/header/logo.html.twig</code></li>
  <li>to list all overridable templates <code>find vendor/shopware/storefront/Resources/views -type f -name "*.html.twig"</code></li>
  <li>after changes done:
    <ul>
      <li><code>bin/console cache:clear</code></li>
      <li><code>bin/console theme:compile</code></li>
      <li><code>bin/console assets:install</code></li>
    </ul>  
  </li>
</ul>

<h2>View Events</h2>
<ul>
  <li><code>bin/console debug:event</code></li>
</ul>















