/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_ReservationStockUi extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_ReservationStockUi
 */
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/grid/tree-massactions'
], function ($, confirm, alert, Massactions) {
    'use strict';

    return Massactions.extend({

        /**
         * Applies specified action.
         *
         * @param {String} actionIndex - Actions' identifier.
         * @returns {Massactions} Chainable.
         */
        applyAction: function (actionIndex) {
            var action = this.getAction(actionIndex),
                visibility;

            if (action.visible) {
                visibility = action.visible();

                this.hideSubmenus(action.parent);
                action.visible(!visibility);

                return this;
            }
            var data = this.getSelections(),
                callback;

            if (!data.total) {
                alert({
                    content: this.noItemsMsg
                });

                return this;
            }
            callback = this._getCallback(action, data);
            action.confirm ?
                this._confirm(action, callback) :
                callback();

            return this;
        },

        /**
         * Shows actions' confirmation window.
         *
         * @param {Object} action - Actions' data.
         * @param {Function} callback - Callback that will be
         *      invoked if action is confirmed.
         */
        _confirm: function (action, callback) {
            var confirmData = action.confirm,
                data = this.getSelections(),
                total = data.total ? data.total : 0,
                confirmMessage = confirmData.message + ' (' + total + ' record' + (total > 1 ? 's' : '') + ')';

            confirm({
                title: confirmData.title,
                content: confirmMessage,
                actions: {
                    confirm: callback
                }
            });
        }
    });
});
