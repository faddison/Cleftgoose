/**
 * @category Libraries
 * @package Unifom
 * @author JoomlaShine.com
 * @copyright JoomlaShine.com
 * @license GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @version $Id: itemlist.js 19013 2012-11-28 04:48:47Z thailv $
 * @link JoomlaShine.com
 */!function($) {
    /**
     * JSNItemList class
     */
    function JSNItemList(options) {
        this.control = null;
        this.options = $.extend({
            name: 'item-list',
            allowOther: false,
            listItems: [],
            multipleCheck: false
        }, options);

        this.options.value = $.toJSON(this.options.listItems);
        this.template = '<div class="controls"><div class="jsn-buttonbar"><button id="items-list-edit" class="btn btn-small"><i class="icon-pencil"></i>Edit</button><button id="items-list-save" class="btn btn-small btn-primary"><i class="icon-ok"></i>Done</button></div>' +
            '<div class="jsn-items-list ui-sortable">' +
            '{{each(i, val) listItems}}<div class="jsn-item ui-state-default">' +
            '<label class="{{if multipleCheck==true || multipleCheck=="true"}}checkbox{{else}}radio{{/if}}"><input type="{{if multipleCheck==true || multipleCheck=="true"}}checkbox{{else}}radio{{/if}}" value="${val.text}" name="item-list" {{if val.checked == "true" || val.checked == true}}checked{{/if}} />${val.text}</label>' +
            '</div>{{/each}}' +
            '</div>' +
            '{{if allowOther}}<div class="other ui-sortable">' +
            '<div class="jsn-item ui-state-default">' +
            '<label class="{{if multipleCheck==true || multipleCheck=="true"}}checkbox{{else}}radio{{/if}}"><input type="{{if multipleCheck==true || multipleCheck=="true"}}checkbox{{else}}radio{{/if}}" disabled="disabled" value="other" />Other</label>' +
            '<input type="text" value="" disabled="disabled" />' +
            '</div>' +
            '</div>{{/if}}</div>' +
            '<input type="hidden" name="${name}" value="json:${value}" id="${id}" />';
    }
    ;

    JSNItemList.prototype = {
        /**
         * Update list of items to hidden field
         * @return void
         */
        updateItems: function() {
            var items = this.control.find('input[type="checkbox"],input[type="radio"]');
            var listItems = [];

            items.each(function(index, item) {
                listItems.push({
                    text: item.value,
                    checked: item.checked
                });
            });

            $('#' + this.options.id).val('json:' + $.toJSON(listItems));
            $('#' + this.options.id).trigger('change');
        },
        /**
         * Register event handling for elements
         * @return void
         */
        addEvents: function() {
            var self = this;
            var listItems = [];
            var itemChecked = [];
            this.control.find('a.jsn-ic-move').click(function() {
                return false;
            });
            this.control.find('div.jsn-items-list input').click(function(e) {
                e.stopPropagation();
            });
            this.control.find('input[type="checkbox"],input[type="radio"]').change(function() {
                self.updateItems();
            });
            this.control.find("#items-list-edit").click(function() {
                $(this).hide();
                listItems = [];
                itemChecked = [];
                self.control.find(".jsn-items-list .jsn-item").each(function() {
                    listItems.push($(this).find("input").val())
                    if($(this).find("input").is(':checked')) {
                        itemChecked.push($(this).find("input:checked").val());
                    }
                });
                self.control.find(".jsn-items-list").hide().after(
                    $("<div/>", {
                        "id": "items-list-edit-content"
                    }).append(
                        $("<textarea/>", {
                            "class": "jsn-input-xxlarge-fluid",
                            "rows": "10",
                            "text": listItems.join("\r")
                        })));
                self.control.find("#items-list-save").show();
                self.control.find("#items-list-cancel").show();
                self.control.find("#items-list-edit-content textarea").focus();
            });
            self.control.find("#items-list-save").click(function(e) {
                var divItems = $(this).parent().parent();
                var valueItems = divItems.find("#items-list-edit-content textarea").val().split("\n");
                var classValue = self.options.multipleCheck ? "checkbox" : "radio";
                var addedItems = [];

                self.control.find(".jsn-items-list").html("");
                $.each(valueItems, function(key, value) {
                    if(value && addedItems.indexOf(value) == -1) {
                        addedItems.push(value);

                        var inputItem = "";
                        if($.inArray(value, itemChecked) != -1) {
                            if(self.options.multipleCheck) {
                                inputItem = $("<input/>", {
                                    "type": "checkbox",
                                    "checked": "true",
                                    "name": "item-list",
                                    "value": value
                                });
                            } else {
                                inputItem = $("<input/>", {
                                    "type": "radio",
                                    "checked": "true",
                                    "name": "item-list",
                                    "value": value
                                });
                            }
                        } else {
                            if(self.options.multipleCheck) {
                                inputItem = $("<input/>", {
                                    "type": "checkbox",
                                    "name": "item-list",
                                    "value": value
                                });
                            } else {
                                inputItem = $("<input/>", {
                                    "type": "radio",
                                    "name": "item-list",
                                    "value": value
                                });
                            }
                        }
                        self.control.find(".jsn-items-list").append(
                            $("<div/>", {
                                "class": "jsn-item ui-state-default"
                            }).append(
                                $("<label/>", {
                                    "class": classValue
                                }).append(inputItem).append(
                                    value)))
                    }
                });
                addedItems = [];
                self.control.find(".jsn-items-list").show();
                self.control.find("#items-list-save").hide();
                self.control.find("#items-list-cancel").hide();
                self.control.find("#items-list-edit").show();
                self.control.find("#items-list-edit-content textarea").remove();
                self.updateItems();
                self.control.find('a.jsn-ic-move').click(function() {
                    return false;
                });
                self.control.find('div.jsn-items-list input').click(function(e) {
                    e.stopPropagation();
                });
                self.control.find('input[type="checkbox"],input[type="radio"]').change(function() {
                    self.updateItems();
                });
            });
        },
        /**
         * Render UI for control
         * @return void
         */
        render: function() {
            var self = this;

            this.control = $.tmpl(this.template, this.options);
            this.control.find('div.jsn-items-list').sortable({
                items: 'div.jsn-item',
                axis: 'y',
                forceHelperSize: true,
                connectWith: '.jsn-item',
                placeholder: 'ui-state-highlight',
                update: function() {
                    self.updateItems();
                }
            });
            this.addEvents();
            return this.control;
        }
    };

    /**
     * Register jQuery plugin
     */
    $.itemList = function(options) {
        var control = new JSNItemList(options);
        return control.render();
    };
}(jQuery);