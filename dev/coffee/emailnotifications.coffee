if $ and window.Garnish
    EmailNotification = Garnish.Base.extend(
        index: null
        context: null
        formId: null

        $notificationContainer: null

        init: (context, formId) ->
            @index = Math.floor((Math.random() * 10000) + 1)
            @context = context
            @formId = formId
            @$notificationContainer = $('#formbuilder-email-notifications-container')
            @getMarkup()

        getMarkup: ->
            data =
                index: @index
                context: @context
                formId: @formId

            Craft.postActionRequest 'formBuilderEmailNotifications/addNotification', data, $.proxy(((response, textStatus) ->
                if textStatus == 'success'
                    markup = response.markup
                    @$notificationContainer.append markup

                    $element = $('#email-notification-' + @index)
                    $options = $element.find('.option-item')

                    new FormBuilderSection($element)

                    $options.each (i, el) ->
                        new Option(el)
            ), this)
    )

    Garnish.$doc.ready ->
        if $('.add-email-notification').length
            el = $('.add-email-notification')
            context = el.data 'context'
            formId = el.data 'form-id'
            el.on 'click', (e) ->
                e.preventDefault()
                new EmailNotification(context, formId)