if $ and window.Garnish
    EmailTemplate = Garnish.Base.extend(
        $container: null
        $headerHtml: null
        $bodyHtml: null
        $footerHtml: null

        init: (el) ->
            @$container = $(el)
            @$headerHtml = @$container.find '.email-header'
            @$bodyHtml = @$container.find '.email-body'
            @$footerHtml = @$container.find '.email-footer'

    )

# Update Template From Modal
updateTemplateHtml = (modal, option) ->
    className = modal.$modalInputs[0].className
    $('.template-container .'+className).html($(modal.$modalInputs[0]).val()).removeClass 'hidden'
    $('.template-container .'+className).val $(modal.$modalInputs[0]).val()

Garnish.$doc.ready ->
    $('.has-menu').on 'click', (e) ->
        e.preventDefault()
        if $(this).next('.is-menu').hasClass 'show'
            $(this).next('.is-menu').removeClass 'show'
        else
            $(this).next('.is-menu').addClass 'show'

    if $('.template-container').length > 0
        template = new EmailTemplate('.template-container')
        templateContainerHtml = $('.template-container')

        $('.content-header-copy').on 'change', (e) ->
            console.log $(this).val()
            
        $('#templateBackgroundColor').on 'change', (e) ->
            color = $(this).val()
            templateContainerHtml.css 'backgroundColor', color

        $('#templateBorderColor').on 'change', (e) ->
            color = $(this).val()
            templateContainerHtml.css 'borderColor', color

        $('#templateBorderWidth').on 'change input', (e) ->
            width = $(this).val()
            templateContainerHtml.css 'borderWidth', width + 'px'
        
        $('#templateBorderRadius').on 'change input', (e) ->
            radius = $(this).val()
            templateContainerHtml.css 'borderRadius', radius + 'px'

        $('#templateContainerPadding').on 'change input', (e) ->
            padding = $(this).val()
            templateContainerHtml.css 'padding', padding + 'px'

        if $('#templateHeaderLogo').length > 0
            $('#templateHeaderLogo').data('elementSelect').on 'selectElements', (e) ->
                logoHtml = templateContainerHtml.find '.template-logo'
                assetUrl = e.elements[0].url
                logoHtml.html "<img src='#{assetUrl}' >"

            $('#templateHeaderLogo').data('elementSelect').on 'removeElements', (e) ->
                templateContainerHtml.find('.template-logo').html ''
            
            $('#templateHeaderLogoPosition').on 'change', (e) ->
                templateContainerHtml.find('.template-logo').css 'textAlign': $(this).val()
            

        $('.delete-email').on 'click', (e) ->
            e.preventDefault()
            templateId = $(this).data 'id'
            data = id: templateId
            if confirm Craft.t("Are you sure you want to delete this email?")
                Craft.postActionRequest 'formBuilder/email/delete', data, $.proxy(((response, textStatus) ->
                    if textStatus == 'success'
                        window.location.href = '/admin/formbuilder/templates'
                ), this)

    $('.delete-index-email').on 'click', (e) ->
        e.preventDefault()
        templateId = $(this).data 'id'
        templateName = $(this).data 'name'
        data = id: templateId
        if confirm Craft.t("Are you sure you want to delete #{templateName}?")
            Craft.postActionRequest 'formBuilder/email/delete', data, $.proxy(((response, textStatus) ->
                if response.success
                    $row = $('#formbuilder-email-'+templateId)
                    templateTable.sorter.removeItems($row)
                    $row.remove()
                    if response.count == 1
                        $('.templates-table').remove()
                        $('.templates-container').after '<div class="no-templates" id="notemplates"><span class="title">Hello! You don\'t have any templates yet.</span></div>'
                    for hudID of Garnish.HUD.activeHUDs
                        Garnish.HUD.activeHUDs[hudID].hide()
                        Craft.cp.displayNotice Craft.t('Template Deleted')

            ), this)

#    $('.template-actions').each (index, value) ->
#        templateId = $(value).data 'template-id'
#        templateHandle = $(value).data 'template-handle'
#        templateName = $(value).data 'template-name'
#        $menu = $('<div class="template"/>').html(
#            '<ul class="action-item-menu">' +
#                '<li>' +
#                    '<a href="#" class="copy-handle" data-clipboard-text="'+templateHandle+'">' +
#                        'Copy Handle' +
#                    '</a>' +
#                '</li>' +
#                '<li>' +
#                    '<a href="#" class="delete">' +
#                    'Delete</a>' +
#                '</li>' +
#            '</ul>')
#
#        $(value).on 'click', (e) ->
#            e.preventDefault()
#            formbuilderTemplate = new (Garnish.HUD)($(value).find('.template-action-trigger'), $menu,
#                hudClass: 'hud fb-hud formhud'
#                closeOtherHUDs: false)
#
#        $menu.find('.copy-handle').on 'click', (e) ->
#            e.preventDefault()
#            new Clipboard('.copy-handle', text: (trigger) ->
#                templateHandle
#            )
#            for hudID of Garnish.HUD.activeHUDs
#                Garnish.HUD.activeHUDs[hudID].hide()
#
#            Craft.cp.displayNotice Craft.t('Template Handle Copied')
#
#        $menu.find('.delete').on 'click', (e) ->
#            e.preventDefault()
#            data = id: templateId
#            if confirm Craft.t("Are you sure you want to delete #{templateName}?")
#                Craft.postActionRequest 'formBuilder/template/delete', data, $.proxy(((response, textStatus) ->
#                    if response.success
#                        $row = $('#formbuilder-template-'+templateId)
#                        templateTable.sorter.removeItems($row)
#                        $row.remove()
#                        if response.count == 1
#                            $('.templates-table').remove()
#                            $('.templates-container').after '<div class="no-templates" id="notemplates"><span class="title">Hello! You don\'t have any templates yet.</span></div>'
#                        for hudID of Garnish.HUD.activeHUDs
#                            Garnish.HUD.activeHUDs[hudID].hide()
#                            Craft.cp.displayNotice Craft.t('Template Deleted')
#
#                ), this)