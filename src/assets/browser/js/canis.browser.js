function CanisSensorObjectBrowser($element, settings) {
    CanisComponent.call(this);
	this.$element = $element.addClass('browser');
	this.items = {};
	this.elements = {};
	this.selectedObject = false;
	this.settings = jQuery.extend(true, {}, this.defaultSettings, settings);
	this.init();

	this._handleResponse(this.settings);
	this.scheduleRefresh();
	this.$element.on('refresh', function() {
		_this._refresh();
	});

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
};

CanisSensorObjectBrowser.prototype._handleResponse = function(data) {
	var _this = this;
	var foundItems = false;
	var refreshTime = Date.now();
	var catSize = _.size(data.objects);
	if (this.elements.categories === undefined) {
		this.elements.categories = {};
	}
	jQuery.each(data.objects, function(index, category) {
		if (_this.elements.categories[index] === undefined) {
			_this.elements.categories[index] = {'items': {}};
		}
		_this.elements.categories[index].refreshTime = refreshTime;
		if (_this.elements.categories[index].$list === undefined) {
			_this.elements.categories[index].$list = $("<div />", {'class': 'list-group list-group-menu list-group-menu-' + catSize}).appendTo(_this.elements.$menu);
		}
		if (_this.elements.categories[index].$title === undefined) {
			_this.elements.categories[index].$title = $("<div />", {'class': 'list-group-item disabled list-group-header'}).appendTo(_this.elements.categories[index].$list);
		}
		_this.elements.categories[index].$title.html(category.label);

		jQuery.each(category.items, function(key, item) {
			foundItems = true;
			if (_this.elements.categories[index]['items'][key] === undefined) {
				_this.elements.categories[index]['items'][key] = {};
			}
			_this.elements.categories[index]['items'][key].refreshTime = refreshTime;
			if (_this.elements.categories[index]['items'][key].$link === undefined) {
				_this.elements.categories[index]['items'][key].$link = $("<a />", {'class': 'list-group-item', 'href': item.url}).appendTo(_this.elements.categories[index].$list);
				_this.elements.categories[index]['items'][key].$link.click(function() {
					_this.selectObject($(this).data('item'));
					return false;
				});
			}
			_this.elements.categories[index]['items'][key].$link.html(item.label).attr({'href': item.url}).data('item', item);
			_this.elements.categories[index]['items'][key].$link.removeClass('list-group-item-success list-group-item-info list-group-item-danger list-group-item-warning');
	
			if (item.state !== undefined) {
				_this.elements.categories[index]['items'][key].$link.addClass('list-group-item-' + item.state);
			}
		});
	});
	jQuery.each(_this.elements.categories, function (ci, cv) {
		if (cv.refreshTime !== refreshTime) {
			cv.$list.remove();
			delete _this.elements.categories[ci];
		} else {
			jQuery.each(_this.elements.categories[ci].items, function (i, v) {
				if (v.refreshTime !== refreshTime) {
					v.$link.remove();
					delete _this.elements.categories[ci].items[i];
				}
			});
		}
	});
	if (!foundItems) {
		this.elements.$notice.show().html('No items were found');
	} else {
		this.elements.$notice.show().hide();
	}
};

CanisSensorObjectBrowser.prototype.scheduleRefresh = function() {
	var _this = this;
	if (this.scheduledRefresh !== undefined) {
		clearTimeout(this.scheduledRefresh);
	}

    if (this.removalSetup === undefined) {
        this.removalSetup = true;
        this.elements.$canvas.on('remove', function() {
            clearTimeout(_this.scheduledRefresh);
            _this.elements.$canvas.removed = true;
        });
    }
    if (_this.elements.$canvas.removed !== undefined) {
    	return;
    }
	this.scheduledRefresh = setTimeout(function() {
		_this._refresh();
	}, 5000);
};

CanisSensorObjectBrowser.prototype._refresh = function() {
	var _this = this;
	var ajaxSettings = {};
	ajaxSettings.url = this.settings.url;
	ajaxSettings.complete = function() {
		_this.scheduleRefresh();
	};
	ajaxSettings.success = function(data) {
		_this._handleResponse(data);
	};
	jQuery.ajax(ajaxSettings);
	if (this.selectedObject) {
		if (this.refreshContentTimer !== undefined) {
			this.refreshContentTimer.abort();
		}
		var ajax = {'url': this.selectedObject.url};
		ajax.success = function(response) {
			_this.elements.$content.html(response.content);
		};
		this.refreshContentTimer = jQuery.ajax(ajax);
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

	if (this.refreshContentTimer !== undefined) {
		this.refreshContentTimer.abort();
	}
	this.selectedObject = false;
	var ajax = {'url': item.url};
	ajax.success = function(response) {
		_this.selectedObject = item;
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