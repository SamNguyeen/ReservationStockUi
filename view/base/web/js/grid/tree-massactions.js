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
    'mage/template',
    'text!Magenest_ReservationStockUi/template/popup/confirm.html',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/grid/tree-massactions',
    'mage/translate'
], function ($, template, confirmTpl, alert, modal, Massactions, $t) {
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
            let confirmData = action.confirm,
                data = this.getSelections(),
                total = data.total ? data.total : 0,
                confirmMessage = confirmData.message + ' (' + total + ' record' + (total > 1 ? 's' : '') + ')';
            this.confirmModal = $('<div/>').modal({
                title: confirmData.title,
                modalClass: 'reservation-confirmation-modal',
                buttons: [
                    {
                        text: $.mage.__('Cancel'),
                        class: 'action-secondary action-dismiss',
                        attr: {},
                        click: function (event) {
                            $('#confirm-error-message').attr('style', 'display:none');
                            this.closeModal(event);
                        }
                    },
                    {
                        text: $.mage.__('Confirm Delete'),
                        class: 'action-primary action-accept',
                        attr: {},
                        click: function () {
                            $('#confirm-error-message').attr('style', 'display:none');
                            let confirmText = $('#delete-reservation-confirm').val().toLowerCase();
                            if (confirmText == 'confirm') {
                                callback();
                            } else {
                                $('#confirm-error-message').attr('style', 'display:block');
                            }
                        }
                    }
                ],
            });
            this.confirmModal.modal('openModal').append(this._getContent(confirmMessage));

            return false;
        },

        _getContent: function (confirmMessage) {
            var confirmPopup = template(confirmTpl, {
                confirmMessage: confirmMessage,
                noticeMsg: $.mage.__('Once you confirm and press <strong>Confirm Delete</strong>, it cannot be undone or recovered.'),
                confirmMsg: $.mage.__('To confirm, please type: <em>confirm</em>'),
                errorMsg: $.mage.__('Please type confirm in this field.')
            });

            return confirmPopup;
        }
    });
});
