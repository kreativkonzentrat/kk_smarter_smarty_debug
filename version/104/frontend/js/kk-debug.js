/**
 * kk_smarter_smarty_debugging js
 *
 * @package     kk_smarter_smarty_debug
 * @version     104
 * @createdAt   11.06.13 - 15:00
 * @author      Kreativkonzentrat, Felix Moche, Martin Zilz, Niels Baumbach & Special guest Thomas Zilz
 * @copyright   2013 Kreativkonzentrat GbR
 */

(function ($) {
	var debugOpen = false,
		kkSetDebugWindowVisible,
		kkToggleDebugWindowVisible;
	kkSetDebugWindowVisible = function (visible) {
		debugOpen = visible;
		if (debugOpen) {
			$('body').addClass('kk-debug-open');
			$('#kk-debug-content').find('#kk-debug-searchbox').focus();
		} else {
			$('body').removeClass('kk-debug-open');
			$('#kk-debug-searchbox').blur();
		}
	};

	kkToggleDebugWindowVisible = function () {
		kkSetDebugWindowVisible(!debugOpen);
	};

	kk_smarter_smarty_debug.selectPath = function (element, event) {
		var doc = document,
			range,
			selection;
		if (doc.body.createTextRange) {
			range = document.body.createTextRange();
			range.moveToElementText(element);
			range.select();
		} else if (window.getSelection) {
			selection = window.getSelection();
			range = document.createRange();
			range.selectNodeContents(element);
			selection.removeAllRanges();
			selection.addRange(range);
		}
		if (event) {
			event.cancelBubble = true;
		}
	};

	$(function () {
		$('#kk-debug-content-toggle').click(function () {
			kkToggleDebugWindowVisible();
		});

		//select the path
		$('.kk-debug-heading').click(function () {
			$(this).find('.kk-debug-path').selectText();
		});

		$('#kk-debug-show').click(function (evt) {
			evt.preventDefault();
			kkSetDebugWindowVisible(true);
			$('#kk-debug-searchbox').focus().select();
		});

		$('#kk-debug-hide').click(function (evt) {
			evt.preventDefault();
			kkSetDebugWindowVisible(false);
		});

		//listen to key press to open debug window
		$(document).bind('keydown', function (evt) {
			//CTRL + ENTER => show
			if (evt.ctrlKey && (evt.which === 13)) {
				evt.preventDefault();
				evt.stopPropagation();
				evt.handled = true;
				kkSetDebugWindowVisible(true);
				$('#kk-debug-searchbox').focus().select();
			}
			//ESC => close
			if (evt.which === 27) {
				evt.preventDefault();
				kkSetDebugWindowVisible(false);
			}
		});

		var secIdx = 0,					//a continous index to identify sections
			sections = {},				//a collection of all sections (key: sectionIndex, value: section)
			refIdx = 0,					//a continous index to identify object references to a data node 
			refMap = {};				//a map storing a referece to every single data node

		/**
		 * make the debug objects usable
		 * @param node
		 * @returns {*}
		 */
		var transformDebugOutput = function (node) {
			//add a reference id to every node
			var nodeIdx = refIdx++;
			node.idx = nodeIdx;
			refMap[nodeIdx] = node;
			//initialize the filter array
			node.filter = [];
			if (typeof node.children === 'object') {
				//set expanded state
				node.expanded = node.type === 'section';
				//transform any child nodes
				for (var key in node.children) {
					transformDebugOutput(node.children[key]);
				}
			}
			return node;
		};

		/**
		 * sanitize a value with html content, so the output does not render as HTML in the browser but as the actual code
		 * @param value
		 * @returns {*}
		 */
		var sanitize = function (value) {
			if (typeof value === 'string') {
				return $('<div/>').text(value).html();
			}
			return value;
		};

		/**
		 * count the number of children of a node
		 * @param node
		 * @returns {number}
		 */
		var countChildren = function (node) {
			if (typeof node.children !== 'object') return 0;
			var count = 0,
				childIdx;
			for (childIdx in node.children) {
				count++;
			}
			return count;
		};

		/**
		 * create a html node out of a data node
		 * @param node
		 * @param secIdx
		 * @returns {string}
		 */
		var createNode = function (node, secIdx) {
			var jNode = '',
				childName;
			//do not render a not if it is filtered out
			if (node.filter.indexOf('found-nothing') !== -1 && node.filter.indexOf('found-parent') === -1) return '';

			if (node.type === 'section') {
				//render section
				jNode =
					'<div class="kk-debug-section section-idx-' + secIdx + '">' +
						'<div class="kk-debug-section-heading" onclick="kk_smarter_smarty_debug.toggleExpanded(' + node.idx + ',' + secIdx + ');">' +
						node.name +
						(countChildren(node) > 0 ?
							'<span class="' + (node.expanded ? 'toggle minus' : 'toggle') + '">' + (node.expanded ? '-' : '+') + '</span>' :
							''
							) +
						'</div>' +
						'<div class="kk-debug-section-content">';
				for (childName in node.children) {
					if (node.expanded === true) {
						jNode += createNode(node.children[childName], secIdx);
					}
				}
				jNode += '</div></div>';
			} else if (node.type === 'object' || node.type === 'array' || node.type === 'assoc_array') {
				//render objects or arrays
				jNode =
					'<div class="kk-debug-details expandable' + (node.expanded ? ' click-parent' : '') + '">' +
						'<span class="kk-debug-heading kk-debug-attribute expandable" onclick="kk_smarter_smarty_debug.toggleExpanded(' + node.idx + ',' + secIdx + ');">' +
						'<span class="key' + (node.filter.indexOf('found-key') !== -1 ? ' kk-found-element' : '') + '">' + node.key + '</span>' +
						' : ' +
						'<span class="type">' + node.type + (node.type === 'array' || node.type === 'assoc_array' ? ' (' + node.length + ')' : '') + '</span>' +
						'<span class="kk-debug-path' + (node.filter.indexOf('found-path') !== -1 ? ' kk-found-element' : '') + '" onclick="kk_smarter_smarty_debug.selectPath(this, event);">' + node.path + '</span>' +
						(node.filter.indexOf('found-parent') !== -1 ? '<span class="kk-show-more-button" title="alle anzeigen" data-nodeIdx=' + node.idx + ' data-secidx=' + secIdx + '>...</span>' : '') +
						'</span>' +
						'<div class="kk-debug-wrapper click-parent">';
				for (childName in node.children) {
					if (node.expanded == true) {
						jNode += createNode(node.children[childName], secIdx);
					}
				}
				jNode += '</div></div>';
			} else {
				//render literals
				jNode =
					'<div class="kk-deubg-details">' +
						'<span class="kk-debug-heading kk-debug-attribute">' +
						'<span class="key' + (node.filter.indexOf('found-key') !== -1 ? ' kk-found-element' : '') + '">' + node.key + '</span>' +
						' : ' +
						'<span class="value ' + node.type + (node.filter.indexOf('found-value') !== -1 ? ' kk-found-element' : '') + '">' + ((node.type === 'string' && node.value.length > 500) ? ((sanitize(node.value)).substr(0, 500) + '[...]') : sanitize(node.value)) + '</span>' +
						'<span class="kk-debug-path' + (node.filter.indexOf('found-path') !== -1 ? ' kk-found-element' : '') + '" onclick="kk_smarter_smarty_debug.selectPath(this, event);">' + node.path + '</span>' +
						'</span>' +
						'</div>';
			}
			return jNode;
		};

		/**
		 * toggle the expanded state of a data node
		 * @param nodeIdx
		 * @param secIdx
		 */
		kk_smarter_smarty_debug.toggleExpanded = function (nodeIdx, secIdx) {
			var node = refMap[nodeIdx];
			if (typeof node === 'object') {
				node.expanded = !node.expanded;
				if (typeof node.children === 'object' && node.filter.indexOf('found-parent') === -1) {
					for (var childName in node.children) {
						var child = node.children[childName];
						var foundNothingIdx = child.filter.indexOf('found-nothing');
						if (foundNothingIdx !== -1) {
							child.filter.splice(foundNothingIdx, 1);
						}
					}
				}
				renderSection(sections[secIdx]);
			}
		};

		/**
		 * collapse all data nodes
		 * @param node
		 */
		var collapseAll = function (node) {
			if (typeof node.expanded !== 'undefined') node.expanded = false;
			if (typeof node.children === 'object') {
				for (var key in node.children) {
					collapseAll(node.children[key]);
				}
			}
		};

		/**
		 * reset the filter on all data nodes
		 * @param node
		 */
		var resetFilter = function (node) {
			node.filter = [];
			if (typeof node.expanded !== 'undefined') {
				node.expanded = node.type === 'section';
			}
			if (typeof node.children === 'object') {
				for (var key in node.children) {
					resetFilter(node.children[key]);
				}
			}
		};

		/**
		 * make all child nodes visible
		 * @param nodeIdx
		 * @param secIdx
		 */
		var showAllChildren = function (nodeIdx, secIdx) {
			var node = refMap[nodeIdx],
				foundParentIdx = node.filter.indexOf('found-parent'),
				child;
			if (foundParentIdx !== -1) {
				node.filter.splice(foundParentIdx, 1);
			}
			var foundNothingIdx = node.filter.indexOf('found-nothing');
			if (foundNothingIdx !== -1) {
				node.filter.splice(foundNothingIdx, 1);
			}
			if (typeof node.children === 'object') {
				for (var childName in node.children) {
					child = node.children[childName];
					foundNothingIdx = child.filter.indexOf('found-nothing');
					if (foundNothingIdx !== -1) {
						child.filter.splice(foundNothingIdx, 1);
					}
				}
				renderSection(sections[secIdx]);
			}
		};

		/**
		 * adds filters to a node
		 * @param term
		 * @param node
		 * @returns {Array}
		 */
		var filterNode = function (term, node) {
			term = term.toLowerCase();
			var filter = [],
				key = ('' + node.key + '').toLowerCase(),
				value = ('' + node.value + '').toLowerCase(),
				path = (typeof node.path !== 'undefined' && node.path !== null) ? node.path.toLowerCase() : 'ERROR';

			if (term.indexOf('"') === 0 && term.lastIndexOf('"') === term.length - 1) {
				//exact search
				term = term.substr(1, term.length - 2);
				if (key === term) filter.push('found-key');
				if (value === term) filter.push('found-value');
			} else if (term.indexOf('$') === 0 || term.indexOf('>') === 0) {
				//search in path
				term = term.substr(1);
				if (path.indexOf(term) !== -1) {
					filter.push('found-path');
				}
			} else if (term.indexOf('=') === 0) {
				//search in value
				if (term.indexOf('"') === 1 && term.lastIndexOf('"') === term.length - 1) {
					//exact search in value
					term = term.substr(2, term.length - 3);
					if (value === term) filter.push('found-value');
				} else {
					term = term.substr(1);
					if (value.indexOf(term) !== -1) filter.push('found-value');
				}
			} else {
				//search in key and value
				if (key.indexOf(term) !== -1) filter.push('found-key');
				if (value.indexOf(term) !== -1) filter.push('found-value');
			}
			return filter;
		};

		/**
		 * filter nodes recursively with a search term
		 * @param term
		 * @param node
		 * @param prevNodes
		 */
		var filter = function (term, node, prevNodes) {
			node.filter = [];
			if (typeof node.expanded !== 'undefined') node.expanded = false;
			if (typeof prevNodes === 'undefined') prevNodes = [];

			//search the node
			node.filter = filterNode(term, node);
			var found = node.filter.length > 0,
				i;

			//if we've found something we have to update the list of parent nodes to let them know they are in a branch that has a hit
			if (found === true) {
				window.kk_smarter_smarty_debug.results++;
				for (i = 0; i < prevNodes.length; i++) {
					if (prevNodes[i].filter.indexOf('found-parent') === -1) prevNodes[i].filter.push('found-parent');
					if (typeof prevNodes[i].expanded !== 'undefined') prevNodes[i].expanded = true;
				}
			} else {
				node.filter.push('found-nothing');
			}

			if (typeof node.children === 'object') {
				//add this nodes to the previous nodes collection to pass them to the children nodes
				var myNodes = [],
					childName;
				for (i = 0; i < prevNodes.length; i++) {
					myNodes.push(prevNodes[i]);
				}
				myNodes.push(node);
				for (childName in node.children) {
					filter(term, node.children[childName], myNodes);
				}
			}
		};

		/**
		 * prepare sections stored in kk_debug_vars for later use
		 */
		var prepareSections = function () {

			// a request to a store url like this: "?kk-debug=1&kk-get-session=1" will get the debug output as JSON
			var data = {};
			data[kk_smarter_smarty_debug.enableSmartyDebugParam] = 1;
			data[kk_smarter_smarty_debug.getDebugSessionParam] = 1;
			$.ajax({
				url: '',
				data: data,
				dataType: 'json',
				type: 'GET',
				success: function(res) {
					kk_smarter_smarty_debug.kk_debug_vars = res;
					for (var sectionName in kk_smarter_smarty_debug.kk_debug_vars) {
						var idx = secIdx++;
						var content = transformDebugOutput(kk_smarter_smarty_debug.kk_debug_vars[sectionName]);
						sections[idx] = {
							idx: idx,
							content: content,
							name: sectionName
						}
					}
					$('#kk-debug-info-area').html('');
					renderSections();
				},
				error: function(err) {
					console.log('Error: ', err);
					$('#kk-debug-info-area').html('Error fetching debug objects: ' + JSON.stringify(err));
				}
			});

		};

		/**
		 * render a whole section, add it to the dom
		 * @param section
		 */
		var renderSection = function (section) {
			var jTree = createNode(section.content, section.idx),
				section = $('#kk-debug-content > .section-idx-' + section.idx);
			if (section.length === 0) {
				$('#kk-debug-content').append(jTree);
			} else {
				section.empty().append(jTree);
			}
			$('.kk-show-more-button').click(function (evt) {
				evt.stopPropagation();
				var jNode = $(this);
				showAllChildren(jNode.attr('data-nodeIdx'), jNode.attr('data-secidx'));
			});
		};

		/**
		 * render all sections
		 */
		var renderSections = function () {
			for (var idx in sections) {
				renderSection(sections[idx]);
			}
		};

		/**
		 * execute the filter when a search term is applied
		 */
		var registerSearchHandler = function () {
			$('#kk-debug-searchbox').bind('keydown', function (e) {
				if (e.which === 13 && !e.ctrlKey) {
					var term = $('#kk-debug-searchbox').val(),
						idx;

					if (term.trim().length === 0) {
						//search term is empty, reset all filters
						for (idx in sections) {
							resetFilter(sections[idx].content);
						}
						$('#kk-debug-search-results').text('');
					} else {
						kk_smarter_smarty_debug.results = 0;
						for (idx in sections) {
							filter(term, sections[idx].content, []);
						}
						$('#kk-debug-search-results').text(kk_smarter_smarty_debug.results + ' ' + kk_smarter_smarty_debug.kk_lang_var_search_results);
					}
					renderSections();
				}
			});
		};

		prepareSections();
		registerSearchHandler();

	});

})(jQuery);