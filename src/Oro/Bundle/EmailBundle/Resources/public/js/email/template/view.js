/*global define*/
define(['jquery', 'underscore', 'backbone'
    ], function ($, _, Backbone) {
    'use strict';

    /**
     * @export  oroemail/js/email/template/view
     * @class   oroemail.email.template.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        events: {
            'change': 'selectionChanged'
        },
        target: null,

        /**
         * Constructor
         *
         * @param options {Object}
         */
        initialize: function (options) {
            this.template = $('#emailtemplate-chooser-template').html();
            this.target = options.target;

            this.listenTo(this.collection, 'reset', this.render);
            if (!$(this.target).val()) {
                this.selectionChanged();
            }
        },

        /**
         * onChange event listener
         */
        selectionChanged: function () {
            var entityId = this.$el.val();
            this.collection.setEntityId(entityId.split('\\').join('_'));
            if (entityId) {
                this.collection.fetch({reset: true});
            } else {
                this.collection.reset();
            }
        },

        render: function () {
            $(this.target).val('').trigger('change');
            $(this.target).find('option[value!=""]').remove();
            if (this.collection.models.length > 0) {
                $(this.target).append(_.template(this.template, {entities: this.collection.models}));
            }
        }
    });
});
