function CanisSensorObjectViewer($element, settings) {
    CanisComponent.call(this);
    var _this = this;
	this.$element = $element.addClass('sensor-viewer');
	this.sensorObject = false;
	this.elements = {};
	this.settings = jQuery.extend(true, {}, this.defaultSettings, settings);
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

CanisSensorObjectViewer.prototype = jQuery.extend(true, {}, CanisComponent.prototype);

CanisSensorObjectViewer.prototype.objectClass = 'CanisSensorObjectViewer';

CanisSensorObjectViewer.prototype.defaultSettings = {
};

CanisSensorObjectViewer.prototype.init = function() {
	var _this = this;
	var panelMenu = {};
	var panelHeading = {
		'label': _this.settings.initial.object.descriptor,
		'menu': panelMenu
	};
	if (_this.settings.hideTitle) {
		panelHeading = false;
	}
	this.scheduleRefresh();
	this.$element.on('refresh', function() {
		_this._refresh();
	});
	this.elements.canvas = this.generatePanel(this.$element, panelHeading);
	this.elements.$notice = $("<div />", {'class': 'alert alert-warning'}).html('').appendTo(this.elements.canvas.$body).hide();
};

CanisSensorObjectViewer.prototype._handleResponse = function(data) {
    var _this = this;
    if (data.object === undefined) {
    	this.elements.$notice.html("Invalid server response").show();
    } else {
    	this.elements.$notice.hide();
    }
    if (!this.sensorObject) {
		this.sensorObject = new CanisSensorObject(this, data.object);
	} else {
		this.sensorObject.refresh(data.object);
	}
};

CanisSensorObjectViewer.prototype.scheduleRefresh = function() {
	var _this = this;
	if (this.scheduledRefresh !== undefined) {
		clearTimeout(this.scheduledRefresh);
	}
	if (this.$element === null) {
		return;
	}
	this.scheduledRefresh = setTimeout(function() {
		_this._refresh();
	}, 5000);
};

CanisSensorObjectViewer.prototype._refresh = function() {
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

function CanisSensorObject(manager, item) {
	this.manager = manager;
	this.item = item;
	this.elements = {};
	this.columns = {};
	this.init();
	this.refresh(item);
}

CanisSensorObject.prototype.init = function() {
	this.elements.$canvas = $("<div />", {'class': 'row'}).appendTo(this.manager.elements.canvas.$body);
}

CanisSensorObject.prototype.refresh = function(item) {
	console.log(['refresh', item, this.constructor.name]);
	var _this = this;
	this.item = item;
	var colsToSplit = [];
	var colsLeft = 12;
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
	    var dataTypeClass = 'CanisObjectColumn_' + viewComponent.handler.capitalize();
	    if (window[dataTypeClass] !== undefined) {
	    	if (!_this.columns[index]) {
	    		_this.columns[index] = new window[dataTypeClass](_this, viewComponent);
	    	} else {
	    		_this.columns[index].refresh(viewComponent);
	    	}
	    	if (viewComponent.columns !== undefined) {
	    		_this.columns[index].setColumnSize(viewComponent.columns);
	    		colsLeft = colsLeft - viewComponent.columns;
	    		if (colsLeft <= minimumColSize) {
	    			colsLeft = 12;
	    		}
	    	} else {
	    		colsToSplit.push(_this.columns[index]);
	    	}
	    }
	});

    var colSize = Math.floor(colsLeft / colsToSplit.length);
    if (colSize <= minimumColSize) {
    	colSize = minimumColSize;
    }
    jQuery.each(colsToSplit, function (i, col) {
    	col.setColumnSize(colSize);
    });
};

CanisSensorObject.prototype.show = function() {
	this.elements.$canvas.show();
};
CanisSensorObject.prototype.hide = function() {
	this.elements.$canvas.hide();
};


function CanisObjectColumn(sensorObject, viewComponent) {
	this.sensorObject = sensorObject;
	this.component = viewComponent;
	this.elements = {};
	this.init();
	this.refresh(viewComponent);
}
CanisObjectColumn.prototype = Object.create(CanisComponent.prototype);
CanisObjectColumn.prototype.objectClass = 'CanisObjectColumn';
CanisObjectColumn.prototype.constructor = CanisObjectColumn;

CanisObjectColumn.prototype.init = function() {
	this.elements.$column = $("<div />", {'class': ''}).appendTo(this.sensorObject.elements.$canvas);
	this.elements.panel = this.generatePanel(this.elements.$column, this.getPanelHeading());
	this.elements.$canvas = this.elements.panel.$body;
}

CanisObjectColumn.prototype.getPanelHeading = function() {
	return this.component.handler.capitalize();
};

CanisObjectColumn.prototype.refresh = function(viewComponent) {
	// console.log(['refresh', viewComponent, this.constructor.name]);
	this.viewComponent = viewComponent;

};

CanisObjectColumn.prototype.setColumnSize = function(size) {
	var colSize = 'sm';
	this.elements.$column.removeClass('col-'+colSize+'-1 col-'+colSize+'-2 col-'+colSize+'-3 col-'+colSize+'-4 col-'+colSize+'-5 col-'+colSize+'-6 col-'+colSize+'-7 col-'+colSize+'-8 col-'+colSize+'-9 col-'+colSize+'-10 col-'+colSize+'-11 col-'+colSize+'-12');
	this.elements.$column.addClass('col-'+colSize+'-'+size);
};

CanisObjectColumn.prototype.show = function() {
	this.elements.$column.show();
};
CanisObjectColumn.prototype.hide = function() {
	this.elements.$column.hide();
};
CanisObjectColumn.prototype.parseUrl = function(url, replacements) {
	if (replacements === undefined) {
		replacements = {};
	}
	replacements.objectId = this.sensorObject.item.id;
	jQuery.each(replacements, function(find, replace) {
		url = url.replace('__'+find+'__', replace);
	});
	return url;
};

function CanisObjectColumn_Notes(sensorObject, viewComponent) {
    CanisObjectColumn.call(this, sensorObject, viewComponent);
}
CanisObjectColumn_Notes.prototype = Object.create(CanisObjectColumn.prototype);
CanisObjectColumn_Notes.prototype.objectClass = 'CanisObjectColumn_Notes';
CanisObjectColumn_Notes.prototype.constructor = CanisObjectColumn_Notes;
CanisObjectColumn_Notes.prototype.refresh = function(viewComponent) {
	CanisObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
};

CanisObjectColumn_Notes.prototype.getPanelHeading = function() {
	var heading = {};
	heading.label = this.component.handler.capitalize();
	heading.menu = {};
	heading.menu.createItem = {'label': 'Create', 'icon': 'fa fa-plus', 'background': true, 'url': this.parseUrl(this.component.urls.create)};
	return heading;
};

function CanisObjectColumn_Contacts(sensorObject, viewComponent) {
    CanisObjectColumn.call(this, sensorObject, viewComponent);
}
CanisObjectColumn_Contacts.prototype = Object.create(CanisObjectColumn.prototype);
CanisObjectColumn_Contacts.prototype.objectClass = 'CanisObjectColumn_Contacts';
CanisObjectColumn_Contacts.prototype.constructor = CanisObjectColumn_Contacts;
CanisObjectColumn_Contacts.prototype.refresh = function(viewComponent) {
	CanisObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
};
CanisObjectColumn_Contacts.prototype.getPanelHeading = function() {
	var heading = {};
	heading.label = this.component.handler.capitalize();
	heading.menu = {};
	heading.menu.createItem = {'label': 'Create', 'icon': 'fa fa-plus', 'background': true, 'url': this.component.urls.create};
	return heading;
};



function CanisObjectColumn_Info(sensorObject, viewComponent) {
    CanisObjectColumn.call(this, sensorObject, viewComponent);
}
CanisObjectColumn_Info.prototype = Object.create(CanisObjectColumn.prototype);
CanisObjectColumn_Info.prototype.objectClass = 'CanisObjectColumn_Info';
CanisObjectColumn_Info.prototype.constructor = CanisObjectColumn_Info;


function CanisObjectColumn_Sensors(sensorObject, viewComponent) {
    CanisObjectColumn.call(this, sensorObject, viewComponent);
}
CanisObjectColumn_Sensors.prototype = Object.create(CanisObjectColumn.prototype);
CanisObjectColumn_Sensors.prototype.objectClass = 'CanisObjectColumn_Sensors';
CanisObjectColumn_Sensors.prototype.constructor = CanisObjectColumn_Sensors;
CanisObjectColumn_Sensors.prototype.refresh = function(viewComponent) {
	CanisObjectColumn.prototype.refresh.call(this, viewComponent);
	var _this = this;
};


function CanisObjectColumn_SensorData(sensorObject, viewComponent) {
    CanisObjectColumn.call(this, sensorObject, viewComponent);
}
CanisObjectColumn_SensorData.prototype = Object.create(CanisObjectColumn.prototype);
CanisObjectColumn_SensorData.prototype.objectClass = 'CanisObjectColumn_SensorData';
CanisObjectColumn_SensorData.prototype.constructor = CanisObjectColumn_SensorData;

function CanisObjectColumn_SensorEvents(sensorObject, viewComponent) {
    CanisObjectColumn.call(this, sensorObject, viewComponent);
}
CanisObjectColumn_SensorEvents.prototype = Object.create(CanisObjectColumn.prototype);
CanisObjectColumn_SensorEvents.prototype.objectClass = 'CanisObjectColumn_SensorEvents';
CanisObjectColumn_SensorEvents.prototype.constructor = CanisObjectColumn_SensorEvents;

$preparer.add(function(context) {
	$('[data-object-viewer]', context).each(function() {
		var settings = $(this).data('object-viewer');
		//if (typeof settings === 'object') { return; }
		$(this).data('object-viewer', new CanisSensorObjectViewer($(this), settings));
	});
});
