# GMT EDD Download All
Adds a "Download All" links to your multi-file purchases.

[Download](https://github.com/cferdinandi/gmt-edd-download-all/archive/master.zip)



## Getting Started

Getting started with EDD Download All is as simple as installing a plugin:

1. Upload the `gmt-edd-download-all` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins menu in WordPress.

And that's it, you're done. Nice work!

It's recommended that you also install the [GitHub Updater plugin](https://github.com/afragen/github-updater) to get automatic updates.



## Adding a "Download All" link to your confirmation emails

Add a link using the following format for the `href`:

```html
http://your-url.com/checkout/purchase-confirmation/?payment_id={payment_id}&edd_action=download_all_files
```

***Note:*** *Change `checkout/purchase-confirmation` to match the path to your store's purchase confirmation page.*



## How to Contribute

Please read the [Contribution Guidelines](CONTRIBUTING.md).



## License

The code is available under the [GPLv3](LICENSE.md).