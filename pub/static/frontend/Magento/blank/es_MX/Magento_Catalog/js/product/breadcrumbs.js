/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Theme/js/model/breadcrumb-list'
], function ($, breadcrumbList) {
    'use strict';

    return function (widget) {
        $.widget('mage.breadcrumbs', widget, {
            options: {
                categoryUrlSuffix: '',
                useCategoryPathInUrl: true,
                product: '',
                menuContainer: '[data-action="navigation"] > ul',
                menuContainer2: '.filter-categoria ul '
            },

            /** @inheritdoc */
            _init: function () {
                var menu;

                // render breadcrumbs after navigation menu is loaded.
                menu = $(this.options.menuContainer).data('mageMenu');

                if (typeof menu === 'undefined') {
                    $(this.options.menuContainer).on('menucreate', function () {
                        this._super();
                    }.bind(this));
                } else {
                    this._super();
                }
            },

            /** @inheritdoc */
            _render: function () {
                this._appendCatalogCrumbs();
                this._super();
            },

            /**
             * Append category and product crumbs.
             *
             * @private
             */
            _appendCatalogCrumbs: function () {
                console.log(2);
                var categoryCrumbs = this._resolveCategoryCrumbs();

                categoryCrumbs.forEach(function (crumbInfo) {
                    breadcrumbList.push(crumbInfo);
                });

                if (this.options.product) {
                    breadcrumbList.push(this._getProductCrumb());
                }
            },

            /**
             * Resolve categories crumbs.
             *
             * @return Array
             * @private
             */
            _resolveCategoryCrumbs: function () {
                console.log(1);
                var menuItem = this._resolveCategoryMenuItem(),
                    categoryCrumbs = [];

                console.log("MENU ITEM");
                console.log(menuItem);
                console.log(menuItem.length);
                console.log (menuItem.attr('id'));


                if (menuItem !== null && menuItem.length) {
                    console.log("ENTRO CRUMB");                    
                    categoryCrumbs.unshift(this._getCategoryCrumb(menuItem));
                    if (menuItem.attr('id') != "pro"){
                        if ((menuItem = this._getParentMenuItem(menuItem)) !== null) {
                            categoryCrumbs.unshift(this._getCategoryCrumb(menuItem));
                        }
                    }
                }

                return categoryCrumbs;
            },

            /**
             * Returns crumb data.
             *
             * @param {Object} menuItem
             * @return {Object}
             * @private
             */
            _getCategoryCrumb: function (menuItem) {
                console.log("DS");
                console.log(menuItem);
                var categoryId,
                    categoryName,
                    categoryUrl;
                if (menuItem.attr('id') != "pro"){
                categoryId = /(\d+)/i.exec(menuItem.attr('id'));
                categoryName = menuItem.attr("data-text");
                categoryUrl = menuItem.attr('href');
                }else {
                categoryId = "pr";
                categoryName = "Productos";
                categoryUrl = menuItem.attr('href');
                }

                return {
                    'name': 'category' + categoryId,
                    'label': categoryName,
                    'link': categoryUrl,
                    'title': ''
                };
            },

            /**
             * Returns product crumb.
             *
             * @return {Object}
             * @private
             */
            _getProductCrumb: function () {
                console.log(4);
                return {
                    'name': 'product',
                    'label': this.options.product,
                    'link': '',
                    'title': ''
                };
            },

            /**
             * Find parent menu item for current.
             *
             * @param {Object} menuItem
             * @return {Object|null}
             * @private
             */
            _getParentMenuItem: function (menuItem) {
                /*console.log(3);
                var classes,
                    classNav,
                    parentClass,
                    parentMenuItem = null;

                if (!menuItem) {
                    return null;
                }

                classes = menuItem.parent().attr('class');
                classNav = classes.match(/(nav\-)[0-9]+(\-[0-9]+)+/gi);

                if (classNav) {
                    classNav = classNav[0];
                    parentClass = classNav.substr(0, classNav.lastIndexOf('-'));

                    if (parentClass.lastIndexOf('-') !== -1) {
                        parentMenuItem = $(this.options.menuContainer).find('.' + parentClass + ' > a');
                        console.log("PARENT MENU ITEM");
                        console.log(parentMenuItem);
                        parentMenuItem = parentMenuItem.length ? parentMenuItem : null;
                    }
                }*/
                var parentMenuItem = $(this.options.menuContainer).find("a.prod");

                return parentMenuItem;
            },

            /**
             * Returns category menu item.
             *
             * Tries to resolve category from url or from referrer as fallback and
             * find menu item from navigation menu by category url.
             *
             * @return {Object|null}
             * @private
             */
            _resolveCategoryMenuItem: function () {
                console.log("asd");
                var categoryUrl = this._resolveCategoryUrl(),
                    menu = $(this.options.menuContainer),
                    menu2 = $(this.options.menuContainer2),
                    categoryMenuItem = null;

                    console.log(categoryUrl);
                    console.log(menu);

                if (categoryUrl && menu.length) {
                    console.log("FUE 1");
                    categoryMenuItem = menu.find('a[href="' + categoryUrl + '"]');
                }

                if (categoryMenuItem.length == 0) {
                    console.log("FUE 2");
                    categoryMenuItem = menu2.find('a[href="' + categoryUrl + '"]');
                }
                console.log(categoryMenuItem);

                return categoryMenuItem;
            },

            /**
             * Returns category url.
             *
             * @return {String}
             * @private
             */
            _resolveCategoryUrl: function () {
                var categoryUrl;

                if (this.options.useCategoryPathInUrl) {
                    // In case category path is used in product url - resolve category url from current url.
                    categoryUrl = window.location.href.split('?')[0];
                    categoryUrl = categoryUrl.substring(0, categoryUrl.lastIndexOf('/')) +
                        this.options.categoryUrlSuffix;
                } else {
                    // In other case - try to resolve it from referrer (without parameters).
                    categoryUrl = document.referrer;

                    if (categoryUrl.indexOf('?') > 0) {
                        categoryUrl = categoryUrl.substr(0, categoryUrl.indexOf('?'));
                    }
                }

                return categoryUrl;
            }
        });

        return $.mage.breadcrumbs;
    };
});
