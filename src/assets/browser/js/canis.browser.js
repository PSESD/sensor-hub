function CanisSensorObjectBrowser($element, settings) {
    CanisComponent.call(this);
	this.$element = $element.addClass('browser');
	this.items = {};
	this.elements = {};
	this.settings = jQuery.extend(true, {}, this.defaultSettings, settings);
	console.log(this);
	this.init();
	this.expanded = false;
	this.isInitializing = false;
}

CanisSensorObjectBrowser.prototype = jQuery.extend(true, {}, CanisComponent.prototype);

CanisSensorObjectBrowser.prototype.objectClass = 'CanisSensorObjectBrowser';

CanisSensorObjectBrowser.prototype.defaultSettings = {
};

CanisSensorObjectBrowser.prototype.init = function() {
	var _this = this;
	this.elements.$canvas = $("<div />", {'class': 'row'}).appendTo(this.$element);
	this.elements.$notice = $("<div />", {'class': 'alert alert-warning'}).html('').appendTo(this.elements.$canvas).hide();

	this.elements.$menu = $("<div />", {'class': 'col-md-12'}).appendTo(this.elements.$canvas);
	this.elements.$content = $("<div />", {'class': 'col-md-9'}).hide().appendTo(this.elements.$canvas);
	var foundItems = false;
	var catSize = _.size(this.settings.objects);
	jQuery.each(this.settings.objects, function(index, category) {
		var $list = $("<div />", {'class': 'list-group list-group-menu list-group-menu-' + catSize}).appendTo(_this.elements.$menu);
		var $title = $("<div />", {'class': 'list-group-item disabled list-group-header'}).html(category.label).appendTo($list);
		jQuery.each(category.items, function(key, item) {
			foundItems = true;
			var $link = $("<a />", {'class': 'list-group-item', 'href': item.url}).html(item.label).appendTo($list);
			$link.click(function() {
				_this.selectObject(item);
				return false;
			});
		});
	});
	if (!foundItems) {
		this.elements.$notice.show().html('No items were found');
	}
};


CanisSensorObjectBrowser.prototype.selectObject = function(item) {
	var _this = this;
	if (!this.expanded) {
		this.expanded = true;
		var $panel = this.$element.closest('.modal-sm').switchClass('modal-sm', 'modal-xl', 200);
		this.elements.$menu.removeClass('col-md-12').addClass('col-md-3');
		this.elements.$content.html('Loading').show();
	}
	this.elements.$content.html('Loading...');
	var ajax = {'url': item.url};
	ajax.success = function(response) {
		_this.elements.$content.html(response.content);
	};
	jQuery.ajax(ajax);
};


$preparer.add(function(context) {
	$('[data-object-browser]').each(function() {
		var settings = $(this).data('object-browser');
		$(this).data('object-browser', new CanisSensorObjectBrowser($(this), settings));
	});
});