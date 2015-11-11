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
	this.elements.$infoIcon = $("<span />", {'class': 'text-primary fa fa-info-circle'}).appendTo(this.elements.$titleContainer).hide();
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

CanisItem.prototype.updateInfo = function() {
	if (_.isEmpty(this.item.info)) {
		this.elements.$infoIcon.hide();
	} else {
		var popoverOptions = {};
		popoverOptions.placement = 'bottom';
		popoverOptions.content = $("<table />", {'class': 'table table-striped'});
		popoverOptions.html = true;
		popoverOptions.container = "body";
		popoverOptions.trigger = 'hover';
		jQuery.each(this.item.info, function(label, value) {
			var $row = $("<tr />").appendTo(popoverOptions.content);
			$("<th />").html(label).appendTo($row);
			$("<td />").html(value).appendTo($row);
		});
		popoverOptions.content = popoverOptions.content[0].outerHTML;
		if (this.elements.$infoIcon.data('bs.popover') !== undefined) {
			this.elements.$infoIcon.data('bs.popover').options.content = popoverOptions.content
		} else {
			this.elements.$infoIcon.show().popover(popoverOptions).on("show.bs.popover", function(){ $(this).data("bs.popover").tip().css({maxWidth: "400px"}); });
		}
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
	if (this.elements.$components.elements === undefined) {
		this.elements.$components.elements = {};
	}
	if (!this.item.components) { return; }
	if (_this.elements.$components.elements.$popoverContainer === undefined) {
		_this.elements.$components.elements.$popoverContainer = $("<div />").hide().appendTo($("body"));
	}
	jQuery.each(this.item.components, function(id, component) {
		jQuery.each(component.items, function(cid, item) {
			if (item.state === undefined) {
				item.state = 'primary';
			}
			var key = id+'-'+cid;
			if (_this.elements.$components.elements[key] === undefined) {
				_this.elements.$components.elements[key] = {};
			}
			if (_this.elements.$components.elements[key].$a === undefined) {
				_this.elements.$components.elements[key].$a = $('<a />').appendTo(_this.elements.$components);
			}
			if (_this.elements.$components.elements[key].$label === undefined) {
				_this.elements.$components.elements[key].$label = $('<span />').appendTo(_this.elements.$components.elements[key].$a);
			}
			_this.elements.$components.elements[key].$a.attr({'href': item.url, 'class': 'btn btn-'+item.state});
			_this.elements.$components.elements[key].$label.html(item.label);

			if (item.attributes !== undefined) {
				_this.elements.$components.elements[key].$a.attr(item.attributes);
			}
			if (item.background && item.background === true) {
				_this.elements.$components.elements[key].$a.attr({'data-handler': 'background'});
			}

			if (item.badge !== undefined) {
				if (_this.elements.$components.elements[key].$badge === undefined) {
					_this.elements.$components.elements[key].$badge = $('<span />', {'class': 'badge'}).appendTo(_this.elements.$components.elements[key].$a);
				}
				_this.elements.$components.elements[key].$badge.show().html(item.badge);
			} else if (_this.elements.$components.elements[key].$badge !== undefined) {
				_this.elements.$components.elements[key].$badge.hide();
			}
			if (_this.elements.$components.elements[key].$popover === undefined) {
				_this.elements.$components.elements[key].$popover = $('<div />').appendTo(_this.elements.$components.elements.$popoverContainer);
			}
			if (item.subitems !== undefined && !_.isEmpty(item.subitems)) {
				_this.generateList(item.subitems, _this.elements.$components.elements[key].$popover, item.truncated);

				var popoverOptions = {};
				popoverOptions.placement = 'bottom';
				popoverOptions.content = _this.elements.$components.elements[key].$popover[0].outerHTML;
				popoverOptions.html = true;
				popoverOptions.container = "body";
				popoverOptions.trigger = 'hover';
				if (_this.elements.$components.elements[key].$a.data('bs.popover') !== undefined) {
					_this.elements.$components.elements[key].$a.data('bs.popover').options.content = popoverOptions.content;
				} else {
					_this.elements.$components.elements[key].$a.popover(popoverOptions).on("show.bs.popover", function(){ $(this).data("bs.popover").tip().css({maxWidth: "400px"}); });
				}
			} else {
				if (_this.elements.$components.elements[key].$a.data('bs.popover') !== undefined) {
					_this.elements.$components.elements[key].$a.popover('destroy');
				}
			}
		});
	});
};

CanisItem.prototype.generateList = function(list, $parent, truncated, label) {
	var parentItems = $parent.data('items');
	if (!parentItems) {
		parentItems = {};
	}
	if (truncated === undefined) {
		truncated = false;
	}
	if (parentItems.$childList === undefined) {
		parentItems.$childList = $("<div />", {'class': 'list-group'}).appendTo($parent);
	}
	if (parentItems.$childListLabel === undefined) {
		parentItems.$childListLabel = $("<div />", {'class': 'list-group-item disabled'}).hide().appendTo(parentItems.$childList);
	}
	if (label !== undefined) {
		parentItems.$childListLabel.show().html(label);
	} else {
		parentItems.$childListLabel.hide();
	}
	var self = this;
	jQuery.each(list, function (index, item) {
		if (parentItems[index] === undefined) {
			parentItems[index] = item;
			parentItems[index].elements = {};
		}

		if (parentItems[index].elements.$item === undefined) {
			parentItems[index].elements.$item = $("<div />", {'class': ''}).appendTo(parentItems.$childList);
		}
		
		if (item.subitems !== undefined && !_.isEmpty(item.subitems)) {
			self.generateList(item.subitems, parentItems[index].elements.$item, item.truncated, item.label);
			return true;
		}
		parentItems[index].elements.$item.addClass('list-group-item');


		if (parentItems[index].elements.$label === undefined) {
			parentItems[index].elements.$label = $("<h5 />", {'class': 'list-group-item-heading'}).appendTo(parentItems[index].elements.$item);
		}
		if (parentItems[index].elements.$labelSpan === undefined) {
			parentItems[index].elements.$labelSpan = $("<span />").appendTo(parentItems[index].elements.$label);
		}
		parentItems[index].elements.$labelSpan.html(item.label);

		parentItems[index].elements.$item.removeClass('list-group-item-success list-group-item-info list-group-item-danger list-group-item-warning');
		if (item.state !== undefined) {
			parentItems[index].elements.$item.addClass('list-group-item-' + item.state);
		}

		if (item.badge !== undefined) {
			if (parentItems[index].elements.$badge === undefined) {
				parentItems[index].elements.$badge = $("<span />", {'class': 'badge'}).appendTo(parentItems[index].elements.$label);
			}
			parentItems[index].elements.$badge.show().html(item.badge);
		} else if (parentItems[index].elements.$badge !== undefined) {
			parentItems[index].elements.$badge.hide();
		}
	});
	if (truncated) {
		if (parentItems.$truncated === undefined) {
			parentItems.$truncated = $("<div />", {'class': 'list-group-item list-group-truncated'}).html('<span class="fa fa-ellipsis-h"></span>').appendTo(parentItems.$childList);
		}
		parentItems.$truncated.show();
	} else {
		if (parentItems.$truncated !== undefined) {
			parentItems.$truncated.hide();
		}
	}
	$parent.data('items', parentItems);
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
	this.elements.$title.html(item.descriptor);
	this.updateInfo();
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