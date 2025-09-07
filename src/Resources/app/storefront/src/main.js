import ExamplePlugin from './example-plugin/example-plugin.plugin';
import MinimalOffCanvasCartPlugin from './minimal-offcanvas-cart-plugin/minimal-offcanvas-cart.plugin';

const PluginManager = window.PluginManager;
PluginManager.register('MinimalOffcanvasPlugin', MinimalOffCanvasCartPlugin, '[data-minimal-offcanvas]');
PluginManager.register('ExamplePlugin', ExamplePlugin, '[data-example-plugin]');