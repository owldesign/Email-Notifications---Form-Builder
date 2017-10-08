var EmailTemplate, updateTemplateHtml;

if ($ && window.Garnish) {
  EmailTemplate = Garnish.Base.extend({
    $container: null,
    $headerHtml: null,
    $bodyHtml: null,
    $footerHtml: null,
    init: function(el) {
      this.$container = $(el);
      this.$headerHtml = this.$container.find('.email-header');
      this.$bodyHtml = this.$container.find('.email-body');
      return this.$footerHtml = this.$container.find('.email-footer');
    }
  });
}

updateTemplateHtml = function(modal, option) {
  var className;
  className = modal.$modalInputs[0].className;
  $('.template-container .' + className).html($(modal.$modalInputs[0]).val()).removeClass('hidden');
  return $('.template-container .' + className).val($(modal.$modalInputs[0]).val());
};

Garnish.$doc.ready(function() {
  var template, templateContainerHtml;
  $('.has-menu').on('click', function(e) {
    e.preventDefault();
    if ($(this).next('.is-menu').hasClass('show')) {
      return $(this).next('.is-menu').removeClass('show');
    } else {
      return $(this).next('.is-menu').addClass('show');
    }
  });
  if ($('.template-container').length > 0) {
    template = new EmailTemplate('.template-container');
    templateContainerHtml = $('.template-container');
    $('.content-header-copy').on('change', function(e) {
      return console.log($(this).val());
    });
    $('#templateBackgroundColor').on('change', function(e) {
      var color;
      color = $(this).val();
      return templateContainerHtml.css('backgroundColor', color);
    });
    $('#templateBorderColor').on('change', function(e) {
      var color;
      color = $(this).val();
      return templateContainerHtml.css('borderColor', color);
    });
    $('#templateBorderWidth').on('change input', function(e) {
      var width;
      width = $(this).val();
      return templateContainerHtml.css('borderWidth', width + 'px');
    });
    $('#templateBorderRadius').on('change input', function(e) {
      var radius;
      radius = $(this).val();
      return templateContainerHtml.css('borderRadius', radius + 'px');
    });
    $('#templateContainerPadding').on('change input', function(e) {
      var padding;
      padding = $(this).val();
      return templateContainerHtml.css('padding', padding + 'px');
    });
    if ($('#templateHeaderLogo').length > 0) {
      $('#templateHeaderLogo').data('elementSelect').on('selectElements', function(e) {
        var assetUrl, logoHtml;
        logoHtml = templateContainerHtml.find('.template-logo');
        assetUrl = e.elements[0].url;
        return logoHtml.html("<img src='" + assetUrl + "' >");
      });
      $('#templateHeaderLogo').data('elementSelect').on('removeElements', function(e) {
        return templateContainerHtml.find('.template-logo').html('');
      });
      $('#templateHeaderLogoPosition').on('change', function(e) {
        return templateContainerHtml.find('.template-logo').css({
          'textAlign': $(this).val()
        });
      });
    }
    $('.delete-email').on('click', function(e) {
      var data, templateId;
      e.preventDefault();
      templateId = $(this).data('id');
      data = {
        id: templateId
      };
      if (confirm(Craft.t("Are you sure you want to delete this email?"))) {
        return Craft.postActionRequest('formBuilder/email/delete', data, $.proxy((function(response, textStatus) {
          if (textStatus === 'success') {
            return window.location.href = '/admin/formbuilder/templates';
          }
        }), this));
      }
    });
  }
  return $('.delete-index-email').on('click', function(e) {
    var data, templateId, templateName;
    e.preventDefault();
    templateId = $(this).data('id');
    templateName = $(this).data('name');
    data = {
      id: templateId
    };
    if (confirm(Craft.t("Are you sure you want to delete " + templateName + "?"))) {
      return Craft.postActionRequest('formBuilder/email/delete', data, $.proxy((function(response, textStatus) {
        var $row, hudID, results;
        if (response.success) {
          $row = $('#formbuilder-email-' + templateId);
          templateTable.sorter.removeItems($row);
          $row.remove();
          if (response.count === 1) {
            $('.templates-table').remove();
            $('.templates-container').after('<div class="no-templates" id="notemplates"><span class="title">Hello! You don\'t have any templates yet.</span></div>');
          }
          results = [];
          for (hudID in Garnish.HUD.activeHUDs) {
            Garnish.HUD.activeHUDs[hudID].hide();
            results.push(Craft.cp.displayNotice(Craft.t('Template Deleted')));
          }
          return results;
        }
      }), this));
    }
  });
});
