jQuery(function ($) {
    if ($('#bgc-form form').length) {
        $('#year, #month, #day').selectize();

        var locations = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: 'https://json.astrologyapi.com/v1/geo_details?api_key=' + $('[data-api-key]').data('api-key') + '&query=%QUERY',
                wildcard: '%QUERY'
            },
            limit: 10
        });

        locations.initialize();

        $('#location').typeahead({
            hint: true,
            highlight: true,
            minLength: 2
        }, {
            name: 'city',
            displayKey: 'value',
            source: locations.ttAdapter(),
            limit: 20,
            templates: {
                empty: function (ctx) {
                    var encodedStr = ctx.query.replace(/[\u00A0-\u9999<>\&]/gim, function (i) {
                        return '&#' + i.charCodeAt(0) + ';';
                    });

                    return '<div class="tt-suggestion">Sorry, no location names match <b>' + encodedStr + '</b>.</div>';
                },
                suggestion: function (ctx) {
                    var country = ctx.country || '',

                    s = '<p><strong>' + ctx.asciiname + '</strong>';

                    if (country && typeof ctx.admin1 === 'string' && ctx.admin1.length > 0 && ctx.admin1.indexOf(ctx.asciiname) != 0) {
                        country = ctx.admin1 + ', ' + country;
                    }

                    if (country) {
                        country = ' - <small>' + country + '</small>';
                    }

                    return s + country + '</p>';
                }
            }
        });

        $('#location').on('typeahead:selected', function (evt, item) {
            $('#timezone').val(item.timezone);
        });

        // The rest of the code remains the same

		var form = $('#bgc-form form');

		form.on('submit', function (evt) {
			evt.preventDefault();

			form.addClass('loading')
				.parent()
				.find('.error-message')
				.remove();

			$.post(form.attr('action'), form.serializeArray(), function (data) {
				if (data.status == 'success') {
					window.location.href = data.redirect_to;
				} else {
					$(`<div class="error-message">${data.message}</div>`).insertBefore(form);
				}
			})
				.fail(function () {
					$('<div class="error-message">Oops something went wrong, try again later.</div>').insertBefore(form);
				})
				.always(function () {
					form.removeClass('loading');
				});
		});
	}

	if ($('[data-chart]').length) {
		var data = $('[data-chart]').data('chart');

		for (const [key, value] of Object.entries(data.Design)) {
			$('.design').append(`
				<li>
					<span class="icon-bgc-${key.replace(' ', '-')}"></span>
					${value.Gate}.${value.Line}
				</li>
			`);
		}

		for (const [key, value] of Object.entries(data.Personality)) {
			$('.personality').append(`
				<li>
					<span data-intent="${value.Gate}" class="icon-bgc-${key.replace(' ', '-')}"></span>
					${value.Gate}.${value.Line}
				</li>
			`);
		}

		$('text').each(function () {
			var el = jQuery(this)
				.prev();

			var gateNumber = el.attr('id');

			if (hasGate(data, gateNumber)) {
				el.css('fill', '#000000');

				el.next()
					.css('fill', '#FFFFFF');
			}
		});

		$('[id^=design-], [id^=personality-]') .each(function () {
			var el = $(this);

			var gateNumber = el.attr('id')
				.split('-')[1];

			var designValues = Object.values(data.Design),
				personalityValues = Object.values(data.Personality);

			var designGate = null;

			for (var key in designValues) {
				if (designValues[key].Gate == gateNumber) {
					designGate = true;
				}
			}

			var personalityGate = null;

			for (var key in personalityValues) {
				if (personalityValues[key].Gate == gateNumber) {
					personalityGate = true;
				}
			}

			if (designGate != null && personalityGate != null) {
				$(`#design-${gateNumber}`)
					.css('fill', '#c0c0c0');

				$(`#personality-${gateNumber}`)
					.css('fill', '#B86A4F');

				fixLine(el);
			} else if (designGate != null) {
				el.css('fill', '#c0c0c0');

				fixLine(el);
			} else if (personalityGate != null) {
				el.css('fill', '#B86A4F');

				fixLine(el);
			}
		});

		for (var definedCenter in data['DefinedCenters']) {
			var el = $(`#${data['DefinedCenters'][definedCenter].replace(/\s+/g, '-').toLowerCase()}`);

			if (el.length) {
				el.attr('fill', '#d39556');
			}
		}

		if (data.Variables['Digestion'] == 'right') {
			$('#variable-digestion')
				.removeClass('bgc-left')
				.addClass('bgc-right');
		}

		if (data.Variables['Environment'] == 'right') {
			$('#variable-environment')
				.removeClass('bgc-left')
				.addClass('bgc-right');
		}

		if (data.Variables['Awareness'] == 'right') {
			$('#variable-awareness')
				.removeClass('bgc-left')
				.addClass('bgc-right');
		}

		if (data.Variables['Perspective'] == 'right') {
			$('#variable-perspective')
				.removeClass('bgc-left')
				.addClass('bgc-right');
		}

		$('#chart-properties ul').append(`
			<li>
				<strong>Birth Date (Local):</strong> ${data.Properties['BirthDateLocal']}
			</li>
		`);

		$('#chart-properties ul').append(`
									    <li>
				<strong><span class="qc_wpbot_chat_link " data-intent="Type">Type:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['Type']}">${data.Properties['Type']}</span>
			</li>
		`);

		$('#chart-properties ul').append(`
							    <li>
				<strong><span class="qc_wpbot_chat_link " data-intent="Strategy">Strategy:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['Strategy']}">${data.Properties['Strategy']}</span>
			</li>
		`);

		$('#chart-properties ul').append(`
						    <li>
				<strong><span class="qc_wpbot_chat_link " data-intent="Inner Authority">Inner Authority:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['InnerAuthority']}">${data.Properties['InnerAuthority']}</span>
			</li>
		`);

		$('#chart-properties ul').append(`
				    <li>
				<strong><span class="qc_wpbot_chat_link " data-intent="signature">Signature:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['Signature']}">${data.Properties['Signature']}</span>
			</li>
		`);

		$('#chart-properties ul').append(`
					    <li>
				<strong><span class="qc_wpbot_chat_link " data-intent="Not Self Theme">Not Self Theme:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['NotSelfTheme']}">${data.Properties['NotSelfTheme']}</span>
			</li>
		`);

		$('#chart-properties ul').append(`
				    <li>
				<strong><span class="qc_wpbot_chat_link " data-intent="definiton">Definition:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['Definition']}">${data.Properties['Definition']}</span>
			</li>

		`);

		$('#chart-properties ul').append(`
		    <li>
				<strong><span class="qc_wpbot_chat_link " data-intent="profile">Profile:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['Profile']}">${data.Properties['Profile']}</span>
			</li>
		`);

		$('#chart-properties ul').append(`
			<li>
				<strong><span class="qc_wpbot_chat_link " data-intent="digestion">Digestion:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['Digestion']}">${data.Properties['Digestion']}</span>
			</li>
		`);

		$('#chart-properties ul').append(`
		    			<li>
				<strong><span class="qc_wpbot_chat_link " data-intent="environment">Environment:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['Environment']}">${data.Properties['Environment']}</span>
			</li>
		`);

		$('#chart-properties ul').append(`
					    			<li>
				<strong><span class="qc_wpbot_chat_link " data-intent="sense">Sense:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['Sense']}">${data.Properties['Sense']}</span>
			</li>
		`);

		$('#chart-properties ul').append(`
							    			<li>
				<strong><span class="qc_wpbot_chat_link " data-intent="Incarnation Cross">Incarnation Cross:</span></strong> <span class="qc_wpbot_chat_link " data-intent="${data.Properties['IncarnationCross']}">${data.Properties['IncarnationCross']}</span>
			</li>
		`);

		$('[data-tooltip]').hover(function (evt) {
			$(`<div class="hd-tooltip">${$(this).data('tooltip')}</div>`).offset({ top: evt.clientY, left: evt.clientX })
				.appendTo(document.body);
		}, function () {
			$('.hd-tooltip').remove();
		});
	}

});

function hasGate(data, gateNumber) {
	var design = Object.values(data.Design);

	for (var key in design) {
		if (design[key].Gate == gateNumber) {
			return true;
		}
	}

	var personality = Object.values(data.Personality);

	for (var key in personality) {
		if (personality[key].Gate == gateNumber) {
			return true;
		}
	}

	return false;
}

function fixLine(el) {
	var parent = el.parent();

	if (parent.attr('id') == '20-57-10-34-20-34-10-57') {
		el.show();
	}
}