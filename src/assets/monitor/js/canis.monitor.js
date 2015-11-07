function CanisMonitor($element, settings) {
    CanisComponent.call(this);
	this.$element = $element.addClass('monitor');
	this.items = {};
	this.elements = {};
	this.settings = jQuery.extend(true, {}, this.defaultSettings, settings);
	console.log(this);
	this.init();
	this.isInitializing = false;
}

CanisMonitor.prototype = jQuery.extend(true, {}, CanisComponent.prototype);

CanisMonitor.prototype.objectClass = 'CanisMonitor';

CanisMonitor.prototype.defaultSettings = {
};

CanisMonitor.prototype.init = function() {
	var _this = this;
	var panelMenu = {};
	var panelHeading = {
		'label': _this.settings['title'],
		'menu': panelMenu
	};
	this._refresh();
	this.$element.on('refresh', function() {
		_this._refresh();
	});
	this.elements.canvas = this.generatePanel(this.$element, panelHeading);
	this.elements.$notice = $("<div />", {'class': 'alert alert-warning'}).html('').appendTo(this.elements.canvas.$body).hide();
	this.elements.$list = $("<div />", {'class': 'list-group'}).appendTo(this.elements.canvas.$body);
};

CanisMonitor.prototype._handleResponse = function(data) {
    var _this = this;
    var hasItems = false;
    var current = _.keys(_this.items);
    jQuery.each(data.items, function(id, item) {
    	current = _.without(current, id);
    	hasItems = true;
    	if (_this.items[id] === undefined) {
    		_this.items[id] = new CanisItem(_this, item);
    	}
    	_this.items[id].update(item);
    });
	if (!hasItems) {
		this.elements.$notice.show().html('No '+ _this.settings['name']['plural'] +' are being monitored');
	} else {
		this.elements.$notice.hide();
	}
	jQuery.each(current, function(i, id) {
		_this.items[id].hide();
		delete _this.items[id];
	});
};

CanisMonitor.prototype.scheduleRefresh = function() {
	var _this = this;
	if (this.scheduledRefresh !== undefined) {
		clearTimeout(this.scheduledRefresh);
	}
	this.scheduledRefresh = setTimeout(function() {
		_this._refresh();
	}, 5000);
};

CanisMonitor.prototype._refresh = function() {
	var _this = this;
	var ajaxSettings = {};
	ajaxSettings.url = this.settings.packageUrl;
	ajaxSettings.complete = function() {
		_this.scheduleRefresh();
	};
	ajaxSettings.success = function(data) {
		_this._handleResponse(data);
	};

	jQuery.ajax(ajaxSettings);
};

function CanisItem(manager, item) {
	this.manager = manager;
	this.item = item;
	this.elements = {};
	this.init();
}


CanisItem.prototype.sendAction = function(action) {
	var _this = this;
	_this.startPendingAction(action.label);
	var ajaxSettings = {};
	ajaxSettings.type = 'GET';
	ajaxSettings.data = {
		'id': this.item.id,
		'action': action.id
	};
	ajaxSettings.url = this.manager.settings.actionUrl;
	ajaxSettings.complete = function() {
		_this.clearPendingAction();
	};
	ajaxSettings.success = function(data) {
	};
	jQuery.ajax(ajaxSettings);
};

CanisItem.prototype.clearPendingAction = function() {
	this.elements.actions.$buttonGroup.show();
};

CanisItem.prototype.startPendingAction = function(actionDescription) {
	this.elements.actions.$buttonGroup.hide();
};

CanisItem.prototype.init = function() {
	this.elements.$canvas = $("<div />", {'class': 'list-group-item'}).appendTo(this.manager.elements.$list);
	this.elements.$pendingAction = $("<div />", {'class': 'label label-default'}).hide().appendTo(this.elements.$canvas);
	this.elements.actions = {};
	this.elements.actions.$buttonGroup = $("<div />", {'class': 'btn-group pull-right'}).appendTo(this.elements.$canvas);
	this.elements.actions.$button = $("<a />", {'class': 'btn fa fa-chevron-down dropdown-toggle', 'href': '#', 'data-toggle': 'dropdown'}).appendTo(this.elements.actions.$buttonGroup);
	this.elements.actions.$menu = $("<ul />", {'class': 'dropdown-menu'}).appendTo(this.elements.actions.$buttonGroup);
	this.elements.$webActions = $("<div />", {'class': 'btn-group btn-group-sm pull-right'}).appendTo(this.elements.$canvas);
	
	this.elements.$titleContainer = $("<h4 />", {'class': 'list-group-item-heading'}).appendTo(this.elements.$canvas);
	this.elements.$components = $("<div />", {'class': 'btn-group btn-group pull-right canis-components'}).appendTo(this.elements.$titleContainer);
	this.elements.$title = $("<span />", {'class': ''}).appendTo(this.elements.$titleContainer);
	this.elements.$titleBuffer = $("<span />", {'class': ''}).html(' ').appendTo(this.elements.$titleContainer);
	this.elements.$info = $("<div />", {'class': 'list-group-item-text'}).appendTo(this.elements.$canvas);
	
	this.elements.$serviceStatus = $("<div />", {'class': 'canis-item-service-status'}).hide().appendTo(this.elements.$info);
}

CanisItem.prototype.setState = function(state) {
	this.elements.$canvas.removeClass('list-group-item-success list-group-item-info list-group-item-danger list-group-item-warning');
	if (state) {
		this.elements.$canvas.addClass('list-group-item-'+state);
	}
};

CanisItem.prototype.updateItemActions = function() {
	var _this = this;
	return;
	if (_.isEmpty(this.item.itemActions)) {
		this.elements.actions.$button.hide();
	} else {
		this.elements.actions.$button.show();
		this.elements.actions.$menu.html('');
		jQuery.each(this.item.itemActions, function(id, action) {
			action.id = id;
			var $li = $('<li />').appendTo(_this.elements.actions.$menu);
			var iconExtra = '';
			if (action.icon !== undefined) {
				iconExtra = '<span class="'+ action.icon +'"></span> ';
			}
			var $a = $('<a />', {'href': '#'}).html(iconExtra + action.label).appendTo($li);
			if (action.attributes !== undefined) {
				$a.attr(action.attributes);
			} 
			if (action.url !== undefined) {
				$a.attr({'href': action.url});
				if (action.background !== undefined && action.background) {
					$a.attr({'data-handler': 'background'});
				}
			} else {
				$a.on('click', function(e) {
					_this.elements.actions.$button.dropdown("toggle");
					e.preventDefault();
					_this.sendAction(action);
					return false;
				});
			}
		});
	}
};

CanisItem.prototype.updateWebActions = function() {
	var _this = this;
	return;
	this.elements.$webActions.html('');
	jQuery.each(this.item.webActions, function(id, action) {
		action.id = id;
		var iconExtra = '';
		if (action.icon !== undefined) {
			iconExtra = '<span class="'+ action.icon +'"></span> ';
		}
		var $a = $('<a />', {'href': '#', 'class': 'btn btn-default'}).html(iconExtra + action.label).appendTo(_this.elements.$webActions);
		if (action.attributes !== undefined) {
			$a.attr(action.attributes);
		} 
		if (action.url !== undefined) {
			$a.attr({'href': action.url});
			if (action.background !== undefined && action.background) {
				$a.attr({'data-handler': 'background'});
			}
		} else {
			$a.on('click', function(e) {
				_this.elements.actions.$button.dropdown("toggle");
				e.preventDefault();
				_this.sendAction(action);
				return false;
			});
		}
	});
};

CanisItem.prototype.updateComponents = function() {
	var _this = this;
	this.elements.$components.html('');
	if (!this.item.components) { return; }
	jQuery.each(this.item.components, function(id, component) {
		jQuery.each(component.items, function(cid, item) {
			if (item.state === undefined) {
				item.state = 'default';
			}
			var $a = $('<a />', {'href': item.url, 'class': 'btn btn-'+item.state}).html(item.label).appendTo(_this.elements.$components);
			if (item.attributes !== undefined) {
				$a.attr(item.attributes);
			}
			if (item.badge !== undefined) {
				$a.html($a.html() + ' ');
				var $badge = $('<span />', {'class': 'badge'}).html(item.badge).appendTo($a);
			}
		});
	});
};

CanisItem.prototype.show = function() {
	this.elements.$canvas.show();
};
CanisItem.prototype.hide = function() {
	this.elements.$canvas.hide();
};
CanisItem.prototype.updateUptime = function() {
	
};

CanisItem.prototype.update = function(item) {
	this.item = item;
	this.elements.$title.html(item.object.name);
	
	// this.updateWebActions();
	// this.updateItemActions();
	// this.updateServices();
	this.updateComponents();
}

$(function() {
	$('[data-monitor]').each(function() {
		var settings = $(this).data('monitor');
		$(this).data('monitor', new CanisMonitor($(this), settings));
	});
});