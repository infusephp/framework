/*

Header Params:
	name
	title
	type
	filter
	nosort
	nowrap
	truncate
	enum

Types:
	system - reserved for arbitrary fields (edit, delete, etc.)
	id
	text
	longtext
	number
	boolean
	enum = [db value, string value, keys array, values array]
	password
	date
	hidden
	file = [value, url]
	custom = [value, html]
	html - no form field, just static html

Filter i.e. <a href='mailto:![value[email_address]!'>![value[name]]!</a>

*/

if (typeof angular != 'undefined') {
	
	var app = angular.module('models', ['ngResource','ui.bootstrap','ui.date']);
	
	app.factory('Model', ['$resource', function ($resource) {
		var Model = $resource(
			'/' + module + '/' + modelInfo.plural_key + '/:modelId',
			{ modelId : '@modelId' },
			{
				findAll: { method: 'GET', headers: { Accept: 'application/json'} },
				find: { method: 'GET', headers: { Accept: 'application/json'} },
				delete: { method: 'DELETE', headers: { Accept: 'application/json'} },
				edit: { method: 'PUT', headers: { Accept: 'application/json'} },
				create: { method: 'POST', headers: { Accept: 'application/json'} }
			}
		);
		
		return Model;
	}]);
	
	app.config(['$routeProvider',function($routeProvider) {
			$routeProvider.
			when('/', {
				controller: ModelCntl,
				templateUrl: '/templates/admin/models.html'
			}).
			when('/new', {
				controller: ModelCntl,
				templateUrl: '/templates/admin/editModel.html'
			}).
			when('/:id', {
				controller: ModelCntl,
				templateUrl: '/templates/admin/model.html'
			}).
			when('/:id/edit', {
				controller: ModelCntl,
				templateUrl: '/templates/admin/editModel.html'
			}).
			otherwise({redirectTo:'/'});
	}]);
	
	/* Directives */
	
	app.directive('eatClick', function() {
	    return function(scope, element, attrs) {
	        $(element).click(function(event) {
	            event.preventDefault();
	        });
	    };
	});
	
	app.directive('expandingTextarea', function() {
	    return {
	        restrict: 'A',
	        link: function (scope, element, attrs) {
	       		$(element).expandingTextarea();
	       		
	       		scope.$watch(attrs.ngModel, function (v) {
	       			$(element).expandingTextarea('resize');
	       		});
	        }
	    };
	});
	
	/* Filters */
	
	app.filter('modelValue', function() {
		return function (model, properties, property, truncate) {
					
			// apply filter
			if (typeof(property.filter) == 'string')
			{
				value = property.filter;
				
				for (var i in properties)
				{
					// replace placeholders with values
					var search = '{' + properties[i].name + '}';
					
					if (value.indexOf(search) != -1)
						value = value.replace(
							new RegExp(search, "g"),
							parseModelValue(model, properties[i], truncate));
				}
				
				return value;
			}
			// no filter
			else
				return parseModelValue(model, property, truncate);
		};
	});
	
	var ModelCntl = ['$scope', '$routeParams', '$location', 'Model',
		function($scope, $routeParams, $location, Model) {
			
			$scope.module = module;
			$scope.modelInfo = modelInfo;
			$scope.page = 1;
			$scope.limit = 10;	
			$scope.dialogOptions = {
				backdropFade: true,
				dialogFade:true
			};
			$scope.deleteModel = false;
			$scope.models = [];
			$scope.loading = false;
			
			$scope.loadModels = function() {
				var start = ($scope.page - 1) * $scope.limit;
			
				$scope.loading = true;
			
				Model.findAll({
					search: $scope.query,
					start: start,
					limit: $scope.limit,
					sort: ''
				}, function(result) {
				
					$scope.filtered_count = result.filtered_count,
					$scope.links = result.links,
					$scope.page = result.page,
					$scope.page_count = result.page_count,
					$scope.per_page = result.per_page,
					$scope.total_count = result.total_count
					
					$scope.models = result[$scope.modelInfo.plural_key];
					
					// massage data for client side use
					for (var i in $scope.models)
						massageModelForClient ($scope.models[i], $scope.modelInfo.properties);
					
					$scope.loading = false;
					
				}, function(error) {
				
					$scope.loading = false;
					
				});		
			};
				
			$scope.currentPages = function(n) {
				var pages = [];
				
				var i = 0;
				var start = $scope.page - Math.floor(n/2);
				
				if ($scope.page_count - $scope.page < Math.floor(n/2))
					start -= Math.floor(n/2) - ($scope.page_count - $scope.page);
				
				start = Math.max(start, 1);
				
				var p = start;
		
				while (i < n && p <= $scope.page_count) {
					pages.push(p);
					i++;
					p++;
				}
				
				return pages;
			};
			
			$scope.prevPage = function() {
				$scope.goToPage($scope.page-1);
			};
			
			$scope.nextPage = function() {
				$scope.goToPage($scope.page+1);
			};
			
			$scope.goToPage = function(p) {
				if (p < 1 || p > $scope.page_count)
					return;
				
				if ($scope.page != p) {
					$scope.page = p;
					$scope.loadModels();
				}
			};
			
			$scope.findModel = function(id) {
				$scope.loading = false;
				
				Model.find({
					modelId: id
				}, function (result) {
				
					$scope.model = result[$scope.modelInfo.singular_key];
					
					// the model needs to be massaged
					massageModelForClient ($scope.model, $scope.modelInfo.properties);				
					
					$scope.loading = true;
					
				}, function(error) {
				
					if (error.status == 404)
						$location.path('/');
	
					$scope.loading = false;
	
				});
			};
			
			$scope.deleteModelAsk = function(model) {
				$scope.deleteModel = model;
			};
			
			$scope.deleteModelConfirm = function() {
				
				Model.delete({
					modelId: $scope.deleteModel[$scope.modelInfo.idFieldName]
				}, function(result) {
					if (result.success) {
						if ($routeParams.id)
							$location.path('/');
						else
							$scope.loadModels();
					} else if (result.error && result.error instanceof Array) {
		    			$scope.errors = result.error;
		    		}
				});
				
				$scope.deleteModel = false;
			}
			
			$scope.closeDeleteModal = function() {
				$scope.deleteModel = false;
			};
			
			$scope.saveModel = function() {
			
				$scope.saving = true;
				
				var modelData = clone($scope.model);
				
				// some properties need massaging before being sent to the server
				massageModelForServer(modelData, $scope.modelInfo.properties);
	
				//console.log(modelData);
				
				if ($scope.newModel) {
	
					Model.create(modelData, function(result) {
						$scope.saving = false;
						
						//console.log(result);
		
			    		if (result.success) {
			    			$location.path('/');
						} else if (result.error && result.error instanceof Array) {
			    			$scope.errors = result.error;
			    		}
					});
					
				} else {
				
					modelData.modelId = $scope.model[$scope.modelInfo.idFieldName];
				
					Model.edit(modelData, function(result) {
						$scope.saving = false;
						
						//console.log(result);
		
			    		if (result.success) {
			    			$location.path('/' + modelData[$scope.modelInfo.idFieldName]);
						} else if (result.error && result.error instanceof Array) {
			    			$scope.errors = result.error;
			    		}
					});
				
				}
				
			};
			
			if( $routeParams.id )
			{
				$scope.findModel($routeParams.id);
			}
			else
			{
				// new model
				if ($location.$$path.indexOf('/new') !== -1) {
					$scope.newModel = true;
					
					$scope.model = {};
					
					// setup default values
					for (var i in $scope.modelInfo.properties) {
						var property = $scope.modelInfo.properties[i];
						
						$scope.model[property.name] = (typeof property.default != 'undefined') ? property.default : '';
						
						// enums cannot have an empty value, grab first value
						if (property.type == 'enum' && typeof property.default == 'undefined') {
							var kyz = Object.keys(property.enum);
							$scope.model[property.name] = property.enum[kyz[0]];
						}
					}
					
					// the model needs to be massaged
					massageModelForClient ($scope.model, $scope.modelInfo.properties);
				
				// browsing all models
				} else {
					$scope.loadModels();
				}
			}
	}];
	
	function nl2br(input) {
		return (input + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br />$2');
	}
	
	function htmlentities(str) {
		return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}
	
	function parseModelValue (model, property, truncate) {
	
		if (typeof model == 'undefined')
			return '';
		
		var value = '';
		
		if (typeof model[property.name] != 'undefined')
			value = model[property.name];
		else if (property.default)
			value = property.default;
		
		if (value === null)
			return '<em>null</em>';
	
		switch(property.type)
		{
		case 'id':
		case 'number':		
		case 'hidden':
		case 'custom':
		case 'text':
		case 'longtext':
		break;
		case 'boolean':
			value = (value > 0) ? 'Yes' : 'No';
		break;
		case 'enum':
			if (property.enum)
			{
				if (property.enum[value])
					value = property.enum[value];
				else if (!property.default)
					value = property.enum[property.default];
			}		
		break;
		case 'password':
			return '<em>hidden</em>';
		break;
		case 'date':
			value = moment(value).format("M/D/YYYY h:mm a");
		break;
		case 'html':
			return value;
		break;
		}
		
		// truncation
		if (truncate && property.truncate && value.length > 40)
			value = value.substring(0, 40) + '...';
		
		return nl2br(htmlentities(value));
	}
	
	function massageModelForClient (model, properties) {
	
		for (var i in properties) {
			var property = properties[i];
			var value = model[property.name];
			
			switch (property.type)
			{
			case 'date':
				if (value == 0)
					model[property.name] = new Date();
				else
					model[property.name] = moment.unix(value).toDate();
			break;
			case 'boolean':
				model[property.name] = (value > 0) ? true : false;
			break;
			}
			
			if (property.null) {
			
			}
		}
	
	}
	
	function massageModelForServer (model, properties) {
	
		for (var i in properties) {
			var property = properties[i];
			var value = model[property.name];
		
			switch (property.type)
			{
			case 'date':
				model[property.name] = moment(value).unix();
			break;
			case 'password':
				if (value.length == 0)
					delete model[property.name];
			break;
			}
		}
	
	}
	
	// props to http://my.opera.com/GreyWyvern/blog/show.dml/1725165
	
	function clone(obj) {
	    // Handle the 3 simple types, and null or undefined
	    if (null == obj || "object" != typeof obj) return obj;
	
	    // Handle Date
	    if (obj instanceof Date) {
	        var copy = new Date();
	        copy.setTime(obj.getTime());
	        return copy;
	    }
	
	    // Handle Array
	    if (obj instanceof Array) {
	        var copy = [];
	        for (var i = 0, len = obj.length; i < len; i++) {
	            copy[i] = clone(obj[i]);
	        }
	        return copy;
	    }
	
	    // Handle Object
	    if (obj instanceof Object) {
	        var copy = {};
	        for (var attr in obj) {
	            if (obj.hasOwnProperty(attr)) copy[attr] = clone(obj[attr]);
	        }
	        return copy;
	    }
	
	    throw new Error("Unable to copy obj! Its type isn't supported.");
	}
}