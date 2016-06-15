Highcharts.setOptions({
  global: {
    useUTC: false
  }
});

function PsesdSensorObjectViewer($element, settings) {
    CanisComponent.call(this);
    var _this = this;
	this.$element = $element.addClass('sensor-viewer');
	this.sensorObject = false;
	this.previousObject = false;
	this.elements = {};
	this.settings = jQuery.extend(true, {}, this.defaultSettings, settings);
	this.data = this.settings.initial;
	console.log(this);
	this.init();
	this._handleResponse(settings.initial);
	this.isInitializing = false;
	this.$element.on('remove', function() {
		_this.$element = null;
        clearTimeout(_this.scheduledRefresh);
    });
	this.$element.on('refresh', function() {
		_this._refresh();
	});
}

PsesdSensorObjectViewer.prototype = jQuery.extend(true, {}, CanisComponent.prototype);

PsesdSensorObjectViewer.prototype.objectClass = 'PsesdSensorObjectViewer';

PsesdSensorObjectViewer.prototype.defaultSettings = {
};

PsesdSensorObjectViewer.prototype.switchObject = function() {
	var _this = $(this).data('manager');
	var request = {};
	request.url = $(this).attr('href');
	request.data = {'package': 1};
	_this.previousObject = {
		'label': _this.data.object.descriptor,
		'url': _this.settings.packageUrl
	};
	request.success = function (dataPackage) {
		_this.settings.packageUrl = request.url;
		_this.sensorObject = false;
		_this.$element.html('');
		_this.elements = {};
		_this.data = dataPackage;
		_this.init();
		_this._handleResponse(dataPackage);
	};
	jQuery.ajax(request);
	return false;
};

PsesdSensorObjectViewer.prototype.init = function() {
	var _this = this;
	var panelMenu = {};
	var panelHeading = {
		'label': _this.data.object.descriptor,
		'menu': panelMenu
	};
	var $modalTitle = false;
	if (_this.settings.hideTitle) {
		panelHeading = false;
		$modalTitle = _this.$element.closest('.modal-content').children('.modal-header').find('.modal-title');
		$modalTitle.html(_this.data.object.descriptor);
	}
	this.scheduleRefresh();
	this.elements.canvas = this.generatePanel(this.$element, panelHeading);
	if (!$modalTitle) {
		$modalTitle = this.elements.canvas.$title;
	}
	if (this.previousObject) {
		var $backButtonGrp = $("<div />", {'style': 'margin-left: 5px;', 'class': 'btn-group'}).appendTo($modalTitle);
		var $backButton = $("<a />", {'href': this.previousObject.url, 'class': 'btn btn-xs btn-primary'}).data('manager', this).click(this.switchObject).html("<span class='fa fa-caret-left'></span> "+this.previousObject.label).appendTo($backButtonGrp);
	}
	this.elements.$notice = $("<div />", {'class': 'alert alert-warning'}).html('').appendTo(this.elements.canvas.$body).hide();
};

PsesdSensorObjectViewer.prototype._handleResponse = function(data) {
    var _this = this;
    if (data.object === undefined) {
    	this.elements.$notice.html("Invalid server response").show();
    } else {
    	this.elements.$notice.hide();
    }
    if (!this.sensorObject) {
		this.sensorObject = new PsesdSensorObject(this, data.object);
	} else {
		this.sensorObject.refresh(data.object);
	}
};

PsesdSensorObjectViewer.prototype.scheduleRefresh = function() {
	var _this = this;
	if (this.scheduledRefresh !== undefined) {
		clearTimeout(this.scheduledRefresh);
	}
	if (this.$element === null) {
		return;
	}
	this.scheduledRefresh = setTimeout(function() {
		_this._refresh();
	}, 15000);
};

PsesdSensorObjectViewer.prototype._refresh = function() {
	var _this = this;
	var ajaxSettings = {};
	ajaxSettings.url = this.settings.packageUrl;
	ajaxSettings.data = {'package': 1}
	ajaxSettings.complete = function() {
		_this.scheduleRefresh();
	};
	ajaxSettings.success = function(data) {
		_this._handleResponse(data);
	};

	jQuery.ajax(ajaxSettings);
};

function PsesdSensorObject(manager, item) {
	this.manager = manager;
	this.item = item;
	this.elements = {};
	this.columns = {};
	this.init();
	this.refresh(item);
}

PsesdSensorObject.prototype.init = function() {
	this.elements.$canvas = $("<div />", {'class': 'row-container'}).appendTo(this.manager.elements.canvas.$body);
	this.elements.rows = [];
	this.elements.currentRow = {'colsLeft': 12, 'items': [], '$element': $("<div />", {'class': 'row'}).appendTo(this.elements.$canvas)};
	this.elements.rows.push(this.elements.currentRow);
}

PsesdSensorObject.prototype.refresh = function(item) {
	// console.log(['refresh', item, this.constructor.name]);
	var _this = this;
	this.item = item;
	var currentColumn = 0;

	jQuery.each(item.view, function(index, viewComponent) {
		currentColumn++;
		if (viewComponent.priority === undefined) {
			viewComponent.priority = currentColumn; 
		}
	});

	item.view = _.sortBy(item.view, 'priority');

	var minimumColSize = 3;

	jQuery.each(item.view, function(index, viewComponent) {
		if (viewComponent.handler === undefined) {
			return true;
		}
	    var dataTypeClass = 'PsesdObjectColumn_' + viewComponent.handler.capitalize();
	    if (window[dataTypeClass] !== undefined) {
	    	if (viewComponent.columns === undefined) {
	    		viewComponent.columns = viewComponent.minColumns || minimumColSize;
	    	}
	    	if (_this.columns[index] === undefined) {
	    		var leftOver = 0;
	    		if (_this.elements.currentRow.colsLeft < viewComponent.columns) {
	    			_this.fillRow(_this.elements.currentRow);
	    			_this.elements.currentRow = {'colsLeft': 12, 'items': [], '$element': $("<div />", {'class': 'row'}).appendTo(_this.elements.$canvas)};
	    			_this.elements.rows.push(_this.elements.currentRow);
	    		}
		    	_this.elements.currentRow.colsLeft = _this.elements.currentRow.colsLeft - viewComponent.columns;
	    		
	    		_this.columns[index] = new window[dataTypeClass](_this, viewComponent, _this.elements.currentRow.$element);
	    		_this.elements.currentRow.items.push(_this.columns[index]);
	    		_this.columns[index].setColumnSize(viewComponent.columns+leftOver);
	    	} else {
	    		_this.columns[index].refresh(viewComponent);
	    	}
	    }
	});
	_this.fillRow(_this.elements.currentRow);
    
};

PsesdSensorObject.prototype.fillRow = function (row) {
	var lastCols = row.colsLeft;
	while (row.colsLeft > 0) {
		jQuery.each(row.items, function(i, col) {
			col.increaseColumnSize(1);
			row.colsLeft = row.colsLeft - 1;
			if (row.colsLeft === 0) {
				return false;
			}
		});
		if (lastCols === row.colsLeft) {
			break;
		}
		lastCols = row.colsLeft;
	}
};
PsesdSensorObject.prototype.show = function() {
	this.elements.$canvas.show();
};
PsesdSensorObject.prototype.hide = function() {
	this.elements.$canvas.hide();
};


function PsesdObjectColumn(sensorObject, viewComponent, $row) {
	this.sensorObject = sensorObject;
	this.viewComponent = viewComponent;
	this.$parentRow = $row;
	this.elements = {};
	this.init(viewComponent);
	this.refresh(viewComponent);
}
PsesdObjectColumn.prototype = Object.create(CanisComponent.prototype);
PsesdObjectColumn.prototype.objectClass = 'PsesdObjectColumn';
PsesdObjectColumn.prototype.constructor = PsesdObjectColumn;

PsesdObjectColumn.prototype.init = function(viewComponent) {
	this.elements.$column = $("<div />", {'class': ''}).appendTo(this.$parentRow);
	this.elements.panel = this.generatePanel(this.elements.$column, this.getPanelHeading());
	this.elements.$canvas = this.elements.panel.$body;
}

PsesdObjectColumn.prototype.getPanelHeading = function() {
	return this.viewComponent.handler.capitalize();
};

PsesdObjectColumn.prototype.childDialog = function() {
	console.log($(this).attr('href'));
	return false;
};

PsesdObjectColumn.prototype.refresh = function(viewComponent) {
	// console.log(['refresh', viewComponent, this.constructor.name]);
	this.viewComponent = viewComponent;

};

PsesdObjectColumn.prototype.setColumnSize = function(size) {
	var colSize = 'sm';
	this.columnSize = size;
	this.elements.$column.removeClass('col-'+colSize+'-1 col-'+colSize+'-2 col-'+colSize+'-3 col-'+colSize+'-4 col-'+colSize+'-5 col-'+colSize+'-6 col-'+colSize+'-7 col-'+colSize+'-8 col-'+colSize+'-9 col-'+colSize+'-10 col-'+colSize+'-11 col-'+colSize+'-12');
	this.elements.$column.addClass('col-'+colSize+'-'+size);
};

PsesdObjectColumn.prototype.increaseColumnSize = function(size) {
	if (size === undefined) {
		size = 1;
	}
	return this.setColumnSize(size+this.columnSize);
};

PsesdObjectColumn.prototype.show = function() {
	this.elements.$column.show();
};
PsesdObjectColumn.prototype.hide = function() {
	this.elements.$column.hide();
};
PsesdObjectColumn.prototype.parseUrl = function(url, replacements) {
	if (replacements === undefined) {
		replacements = {};
	}
	replacements.objectId = this.sensorObject.item.id;
	jQuery.each(replacements, function(find, replace) {
		url = url.replace('__'+find+'__', replace);
	});
	return url;
};

function PsesdObjectColumn_Notes(sensorObject, viewComponent, $row) {
    this.items = {};
    PsesdObjectColumn.call(this, sensorObject, viewComponent, $row);
}

PsesdObjectColumn_Notes.prototype = Object.create(PsesdObjectColumn.prototype);
PsesdObjectColumn_Notes.prototype.objectClass = 'PsesdObjectColumn_Notes';
PsesdObjectColumn_Notes.prototype.constructor = PsesdObjectColumn_Notes;

PsesdObjectColumn_Notes.prototype.init = function(viewComponent) {
	PsesdObjectColumn.prototype.init.call(this, viewComponent);

    this.elements.$emptyNotice = $("<div />", {'class': 'alert alert-warning'}).hide().html('No notes have been added').appendTo(this.elements.$canvas);
    this.elements.$list = $("<div />", {'class': 'list-group'}).appendTo(this.elements.$canvas);
};
PsesdObjectColumn_Notes.prototype.refresh = function(viewComponent) {
	PsesdObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
	var hasItems = false;
	jQuery.each(this.viewComponent.items, function(index, item) {
		hasItems = true;
		if (_this.items[item.id] === undefined) {
			_this.items[item.id] = {'item': item, 'elements': {}};
			_this.items[item.id].elements.$item = $("<div />", {'class': 'list-group-item'}).appendTo(_this.elements.$list);
			_this.items[item.id].elements.$actions = $("<div />", {'class': 'btn-group btn-group-xs pull-right'}).appendTo(_this.items[item.id].elements.$item);
			_this.items[item.id].elements.$heading = $("<a />", {'href': _this.parseUrl(_this.viewComponent.urls.view, {'id': item.id}), 'data-handler': 'background', 'class': 'list-group-item-heading'}).appendTo(_this.items[item.id].elements.$item);
			_this.items[item.id].elements.$subheader = $("<div />", {'class': 'list-group-item-text'}).appendTo(_this.items[item.id].elements.$item);
			
			_this.items[item.id].elements.$update = $("<a />", {'href': _this.parseUrl(_this.viewComponent.urls.update, {'id': item.id}), 'data-handler': 'background', 'class': 'btn btn-default fa fa-wrench'}).appendTo(_this.items[item.id].elements.$actions);
			_this.items[item.id].elements.$delete = $("<a />", {'href': _this.parseUrl(_this.viewComponent.urls.delete, {'id': item.id}), 'data-handler': 'background', 'class': 'btn btn-danger fa fa-trash-o'}).appendTo(_this.items[item.id].elements.$actions);
			// _this.items[item.id].elements.$heading.click(_this.childDialog);
			// _this.items[item.id].elements.$update.click(_this.childDialog);
			// _this.items[item.id].elements.$delete.click(_this.childDialog);
		}
		_this.items[item.id].elements.$heading.html(item.title);
		_this.items[item.id].elements.$subheader.html(item.date);
	});
	if (hasItems) {
		this.elements.$emptyNotice.hide();
	} else {
		this.elements.$emptyNotice.show();
	}
};

PsesdObjectColumn_Notes.prototype.getPanelHeading = function() {
	var heading = {};
	heading.label = this.viewComponent.handler.capitalize();
	heading.menu = {};
	heading.menu.createItem = {'label': 'Create', 'icon': 'fa fa-plus', 'url': this.parseUrl(this.viewComponent.urls.create)};
	return heading;
};

function PsesdObjectColumn_Contacts(sensorObject, viewComponent, $row) {
    this.items = {};
    PsesdObjectColumn.call(this, sensorObject, viewComponent, $row);
}
PsesdObjectColumn_Contacts.prototype = Object.create(PsesdObjectColumn.prototype);
PsesdObjectColumn_Contacts.prototype.objectClass = 'PsesdObjectColumn_Contacts';
PsesdObjectColumn_Contacts.prototype.constructor = PsesdObjectColumn_Contacts;

PsesdObjectColumn_Contacts.prototype.init = function(viewComponent) {
	PsesdObjectColumn.prototype.init.call(this, viewComponent);

    this.elements.$emptyNotice = $("<div />", {'class': 'alert alert-warning'}).hide().html('No contacts have been added').appendTo(this.elements.$canvas);
    this.elements.$list = $("<div />", {'class': 'list-group'}).appendTo(this.elements.$canvas);
};
PsesdObjectColumn_Contacts.prototype.refresh = function(viewComponent) {
	PsesdObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
	var hasItems = false;
	jQuery.each(this.viewComponent.items, function(index, item) {
		hasItems = true;
		if (_this.items[item.id] === undefined) {
			_this.items[item.id] = {'item': item, 'elements': {}};
			_this.items[item.id].elements.$item = $("<div />", {'class': 'list-group-item'}).appendTo(_this.elements.$list);
			_this.items[item.id].elements.$actions = $("<div />", {'class': 'btn-group btn-group-xs pull-right'}).appendTo(_this.items[item.id].elements.$item);
			_this.items[item.id].elements.$heading = $("<a />", {'href': _this.parseUrl(_this.viewComponent.urls.view, {'id': item.id}), 'data-handler': 'background', 'class': 'list-group-item-heading'}).appendTo(_this.items[item.id].elements.$item);
			_this.items[item.id].elements.$subheader = $("<div />", {'class': 'list-group-item-text'}).appendTo(_this.items[item.id].elements.$item);
			_this.items[item.id].elements.$update = $("<a />", {'href': _this.parseUrl(_this.viewComponent.urls.update, {'id': item.id}), 'data-handler': 'background', 'class': 'btn btn-default fa fa-wrench'}).appendTo(_this.items[item.id].elements.$actions);
			_this.items[item.id].elements.$delete = $("<a />", {'href': _this.parseUrl(_this.viewComponent.urls.delete, {'id': item.id}), 'data-handler': 'background', 'class': 'btn btn-danger fa fa-trash-o'}).appendTo(_this.items[item.id].elements.$actions);
			
			// _this.items[item.id].elements.$heading.click(_this.childDialog);
			// _this.items[item.id].elements.$update.click(_this.childDialog);
			// _this.items[item.id].elements.$delete.click(_this.childDialog);
		}
		_this.items[item.id].elements.$heading.html(item.descriptor);
		var roles = [];
		if (item.is_billing == 1) {
			roles.push('Billing');
		}
		if (item.is_technical == 1) {
			roles.push('Technical');
		}
		_this.items[item.id].elements.$subheader.html(roles.join(', '));
	});
	if (hasItems) {
		this.elements.$emptyNotice.hide();
	} else {
		this.elements.$emptyNotice.show();
	}
};

PsesdObjectColumn_Contacts.prototype.getPanelHeading = function() {
	var heading = {};
	heading.label = this.viewComponent.handler.capitalize();
	heading.menu = {};
	heading.menu.createItem = {'label': 'Create', 'icon': 'fa fa-plus', 'background': true, 'url': this.parseUrl(this.viewComponent.urls.create)};
	return heading;
};



function PsesdObjectColumn_Info(sensorObject, viewComponent, $row) {
    PsesdObjectColumn.call(this, sensorObject, viewComponent, $row);
}
PsesdObjectColumn_Info.prototype = Object.create(PsesdObjectColumn.prototype);
PsesdObjectColumn_Info.prototype.objectClass = 'PsesdObjectColumn_Info';
PsesdObjectColumn_Info.prototype.constructor = PsesdObjectColumn_Info;

PsesdObjectColumn_Info.prototype.init = function(viewComponent) {
	PsesdObjectColumn.prototype.init.call(this, viewComponent);
	this.elements.items = {};
    this.elements.$emptyNotice = $("<div />", {'class': 'alert alert-warning'}).hide().html('No additional information is known').appendTo(this.elements.$canvas);
    this.elements.$table = $("<table />", {'class': 'table table-striped'}).appendTo(this.elements.$canvas);
};
PsesdObjectColumn_Info.prototype.refresh = function(viewComponent) {
	PsesdObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
	jQuery.each(this.sensorObject.item.info, function(label, value) {
		if (_this.elements.items[label] === undefined) {
			_this.elements.items[label] = {};
			_this.elements.items[label].$row = $("<tr />").appendTo(_this.elements.$table);
			_this.elements.items[label].$label = $("<th />").html(label).appendTo(_this.elements.items[label].$row);
			_this.elements.items[label].$value = $("<td />").html(value).appendTo(_this.elements.items[label].$row);
		}
		_this.elements.items[label].$value.html(value);
	});
};

function PsesdObjectColumn_List(sensorObject, viewComponent, $row) {
	this.items = {};
    PsesdObjectColumn.call(this, sensorObject, viewComponent, $row);
}
PsesdObjectColumn_List.prototype = Object.create(PsesdObjectColumn.prototype);
PsesdObjectColumn_List.prototype.objectClass = 'PsesdObjectColumn_List';
PsesdObjectColumn_List.prototype.constructor = PsesdObjectColumn_List;
PsesdObjectColumn_List.prototype.init = function(viewComponent) {
	PsesdObjectColumn.prototype.init.call(this, viewComponent);

    this.elements.$emptyNotice = $("<div />", {'class': 'alert alert-warning'}).hide().html('No '+ this.viewComponent.header.toLowerCase() +' found').appendTo(this.elements.$canvas);
    this.elements.$list = $("<div />", {'class': 'list-group', 'style': 'overflow-y: auto; max-height: 150px'}).appendTo(this.elements.$canvas);
};
PsesdObjectColumn_List.prototype.refresh = function(viewComponent) {
	PsesdObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
	var hasItems = false;
	jQuery.each(this.viewComponent.items, function(itemId, item) {
		hasItems = true;
		if (_this.items[itemId] === undefined) {
			_this.items[itemId] = {'item': item, 'elements': {}};
			_this.items[itemId].elements.$item = $("<a />", {'class': 'list-group-item', 'href': _this.parseUrl(_this.viewComponent.urls.view, {'id': itemId})}).appendTo(_this.elements.$list);
			_this.items[itemId].elements.$heading = $("<div />", {'class': 'list-group-item-heading'}).appendTo(_this.items[itemId].elements.$item);
			_this.items[itemId].elements.$title = $("<span />", {}).appendTo(_this.items[itemId].elements.$heading);
			_this.items[itemId].elements.$subheader = $("<div />", {'class': 'list-group-item-text'}).appendTo(_this.items[itemId].elements.$item);
			_this.items[itemId].elements.$item.data('manager', _this.sensorObject.manager).click(_this.sensorObject.manager.switchObject)
		}
		_this.items[itemId].elements.$item.appendTo(_this.elements.$list);
		_this.items[itemId].elements.$title.html(item.label);
		var roles = [];
		_this.items[itemId].elements.$subheader.html(roles.join(', '));
		_this.items[itemId].elements.$item.removeClass('list-group-item-success list-group-item-info list-group-item-danger list-group-item-warning');
		if (item.state !== undefined) {
			_this.items[itemId].elements.$item.addClass('list-group-item-' + item.state);
		}
		if (item.badge !== undefined) {
			if (_this.items[itemId].elements.$badge === undefined) {
				_this.items[itemId].elements.$badge = $("<span />", {'class': 'badge'}).appendTo(_this.items[itemId].elements.$heading);
			}
			_this.items[itemId].elements.$badge.show().html(item.badge);
		} else if (_this.items[itemId].elements.$badge !== undefined) {
			_this.items[itemId].elements.$badge.hide();
		}
	});
	if (hasItems) {
		this.elements.$emptyNotice.hide();
	} else {
		this.elements.$emptyNotice.show();
	}
};
PsesdObjectColumn_List.prototype.getPanelHeading = function() {
	var heading = {};
	heading.label = this.viewComponent.header;
	return heading;
};


function PsesdObjectColumn_SensorState(sensorObject, viewComponent, $row) {
    PsesdObjectColumn.call(this, sensorObject, viewComponent, $row);
}
PsesdObjectColumn_SensorState.prototype = Object.create(PsesdObjectColumn.prototype);
PsesdObjectColumn_SensorState.prototype.objectClass = 'PsesdObjectColumn_SensorState';
PsesdObjectColumn_SensorState.prototype.constructor = PsesdObjectColumn_SensorState;

PsesdObjectColumn_SensorState.prototype.getPanelHeading = function() {
	var heading = {};
	heading.label = 'Sensor';
	return heading;
};
PsesdObjectColumn_SensorState.prototype.init = function(viewComponent) {
	PsesdObjectColumn.prototype.init.call(this, viewComponent);
    this.elements.$statusList = $("<div />", {'class': 'list-group'}).appendTo(this.elements.$canvas);
    this.elements.$statusItem = $("<div />", {'class': 'list-group-item'}).appendTo(this.elements.$statusList);
    this.elements.$statusItemTitle = $("<h3 />", {'class': 'list-group-item-heading'}).appendTo(this.elements.$statusItem);

    this.elements.$dateItem = $("<div />", {'class': 'list-group-item'}).html("Last checked ").appendTo(this.elements.$statusList);
    this.elements.$dateItemText = $("<time />", {'class': 'timeago', 'datetime': ''}).appendTo(this.elements.$dateItem);
};
PsesdObjectColumn_SensorState.prototype.refresh = function(viewComponent) {
	PsesdObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
	this.elements.$statusItemTitle.html(this.viewComponent.stateDescription);
	this.elements.$statusItem.removeClass('list-group-item-success list-group-item-info list-group-item-danger list-group-item-warning');
	this.elements.$statusItem.addClass('list-group-item-' + this.viewComponent.state);
	this.elements.$dateItemText.attr({'datetime': this.viewComponent.lastUpdate}).timeago();
};




function PsesdObjectColumn_SensorData(sensorObject, viewComponent, $row) {
	this.chart = false;
    PsesdObjectColumn.call(this, sensorObject, viewComponent, $row);
}
PsesdObjectColumn_SensorData.prototype = Object.create(PsesdObjectColumn.prototype);
PsesdObjectColumn_SensorData.prototype.objectClass = 'PsesdObjectColumn_SensorData';
PsesdObjectColumn_SensorData.prototype.constructor = PsesdObjectColumn_SensorData;

PsesdObjectColumn_SensorData.prototype.getPanelHeading = function() {
	var heading = {};
	heading.label = 'Data';
	return heading;
};

PsesdObjectColumn_SensorData.prototype.init = function(viewComponent) {
	PsesdObjectColumn.prototype.init.call(this, viewComponent);
    this.elements.$emptyNotice = $("<div />", {'class': 'alert alert-warning'}).hide().html('No sensor data has been recorded').appendTo(this.elements.$canvas);
    this.elements.$chart = $("<div />", {'class': ''}).appendTo(this.elements.$canvas);
};

PsesdObjectColumn_SensorData.prototype.parseData = function(dataItems) {
	var data = [];
	jQuery.each(dataItems, function(i, item) {
		data.push({'x': new Date(item[0]), 'y': item[1], 'name': item[2]});
	});
	return data;
};

PsesdObjectColumn_SensorData.prototype.initChart = function($chart, data) {
	var options = {};
	options.chart = {'zoomType': 'x'};
	options.title = {'text': false};
	options.xAxis = {'type': 'datetime'};
	options.xAxis.dateTimeLabelFormats = {
		millisecond: '%H:%M:%S.%L',
		second: '%l:%M%:%S%p',
		minute: '%l:%M%p',
		hour: '%l:%M%p',
		day: '%b %e',
		week: '%b %e',
		month: '%b \'%y',
		year: '%Y'
	};
	options.yAxis = {'title': {'text': 'Value'}};
	options.legend = {'enabled': false};
	options.plotOptions = {'area': {}};
	options.plotOptions.area.marker = {'radius': 2};
	options.plotOptions.area.lineWidth = 1;
	options.plotOptions.area.states = {};
	options.plotOptions.area.states.hover = {'lineWidth': 1};
	options.plotOptions.area.threshold = null;
	options.credits = {'enabled': false};
	options.tooltip = {};
	options.tooltip.formatter = function() {
		var desc = '<strong>' + this.point.x.toLocaleString() + '</strong>: ' + this.point.name;
		return desc;
	};

	var series = {'type': 'area', 'name': 'Data', 'data': data};
	options.series = [series];
	$chart.addClass('chart').highcharts(options);
	this.chart = $chart.highcharts();
	return data;
};

PsesdObjectColumn_SensorData.prototype.updateChart = function($chart, data) {
	console.log(['update', data]);
	this.chart.series[0].update({
	    data: data
	}, true);
};

PsesdObjectColumn_SensorData.prototype.refresh = function(viewComponent) {
	PsesdObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
	var data = this.parseData(this.viewComponent.items);
	var hasItems = data.length > 0;
	if (!this.elements.$chart.hasClass('chart')) {
		// set up chart
		this.initChart(this.elements.$chart, data);
	} else {
		// update data
		this.updateChart(this.elements.$chart, data);
	}
	if (hasItems) {
		this.elements.$chart.show();
		this.elements.$emptyNotice.hide();
	} else {
		this.elements.$chart.hide();
		this.elements.$emptyNotice.show();
	}
};


function PsesdObjectColumn_SensorEvents(sensorObject, viewComponent, $row) {
	this.items = {};
    PsesdObjectColumn.call(this, sensorObject, viewComponent, $row);
}
PsesdObjectColumn_SensorEvents.prototype = Object.create(PsesdObjectColumn.prototype);
PsesdObjectColumn_SensorEvents.prototype.objectClass = 'PsesdObjectColumn_SensorEvents';
PsesdObjectColumn_SensorEvents.prototype.constructor = PsesdObjectColumn_SensorEvents;
PsesdObjectColumn_SensorEvents.prototype.getPanelHeading = function() {
	var heading = {};
	heading.label = 'Events';
	return heading;
};

PsesdObjectColumn_SensorEvents.prototype.init = function(viewComponent) {
	PsesdObjectColumn.prototype.init.call(this, viewComponent);
    this.elements.$emptyNotice = $("<div />", {'class': 'alert alert-warning'}).hide().html('No sensor events have been recorded').appendTo(this.elements.$canvas);
    this.elements.$list = $("<div />", {'class': 'list-group', 'style': 'overflow-y: auto; max-height: 300px'}).appendTo(this.elements.$canvas);
};
PsesdObjectColumn_SensorEvents.prototype.refresh = function(viewComponent) {
	PsesdObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
	var hasItems = false;
	jQuery.each(this.viewComponent.items, function(itemId, item) {
		hasItems = true;
		if (_this.items[itemId] === undefined) {
			_this.items[itemId] = {'item': item, 'elements': {}};
			_this.items[itemId].elements.$item = $("<div />", {'class': 'list-group-item'}).prependTo(_this.elements.$list);
			_this.items[itemId].elements.$heading = $("<div />", {'class': 'list-group-item-heading'}).appendTo(_this.items[itemId].elements.$item);
			_this.items[itemId].elements.$title = $("<span />", {}).appendTo(_this.items[itemId].elements.$heading);
			_this.items[itemId].elements.$timestamp = $("<time />", {'class': 'timeago list-group-item-text', 'datetime': item.datetime, 'title': item.datetimeHuman}).appendTo(_this.items[itemId].elements.$item);
		}
		_this.items[itemId].elements.$item.prependTo(_this.elements.$list);
		_this.items[itemId].elements.$title.html(item.event);
		
		_this.items[itemId].elements.$item.removeClass('list-group-item-success list-group-item-info list-group-item-danger list-group-item-warning');
		if (item.state !== undefined) {
			_this.items[itemId].elements.$item.addClass('list-group-item-' + item.state);
		}
		if (item.badge !== undefined) {
			if (_this.items[itemId].elements.$badge === undefined) {
				_this.items[itemId].elements.$badge = $("<span />", {'class': 'badge'}).appendTo(_this.items[itemId].elements.$heading);
			}
			_this.items[itemId].elements.$badge.show().html(item.badge);
		} else if (_this.items[itemId].elements.$badge !== undefined) {
			_this.items[itemId].elements.$badge.hide();
		}
	});
	$('.timeago').timeago('refresh');
	if (hasItems) {
		this.elements.$emptyNotice.hide();
	} else {
		this.elements.$emptyNotice.show();
	}
};

$preparer.add(function(context) {
	$('[data-object-viewer]', context).each(function() {
		var settings = $(this).data('object-viewer');
		//if (typeof settings === 'object') { return; }
		$(this).data('object-viewer', new PsesdSensorObjectViewer($(this), settings));
	});
});
