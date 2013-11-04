jQuery(document).ready(function($){

    // Save button
    $('.t3-icon-document-save-close').bind('click', function() {
        $('#cropForm').submit();
    });

    // Switch cropboxes when tabs are clicked
    $('.typo3-dyntabmenu').delegate('td', 'click', function() {

      var $currentTabNav = $(this),
          $tabPanels = $('.typo3-dyntabmenu-divs').children(),
          index = $currentTabNav.parent().children().index($currentTabNav);

      // Hide all current crop boxes
      $tabPanels.each(function() {
          var currentCB = $(this).data('cropbox');
          currentCB.setOptions({
              hide: true
          });
      });

      // Activate current crop box
      var newCropbox = $tabPanels.eq(index).data('cropbox');
      newCropbox.setOptions({
          hide: false,
          show: true
      });

      var options = newCropbox.getOptions();
      setTimeout(function() {
        updateAr(options.aspectRatio);
      }, 1)

    });

    // Initialize Crop Mask
    $('.typo3-dyntabmenu-divs').children().each(function() {
        var $container = $(this),
            isVisible = ($container.filter(':visible').length == 1),
            $values = $container.find('.values'),
            ar = ($values.attr('data-ratio') ? $values.attr('data-ratio') : null),
            cropbox = $container.find('.cropbox').imgAreaSelect({
                x1: $values.attr('data-x1'),
                x2: $values.attr('data-x2'),
                y1: $values.attr('data-y1'),
                y2: $values.attr('data-y2'),
                aspectRatio: ar,
                imageWidth: $values.attr('data-width'),
                imageHeight: $values.attr('data-height'),
                handles: true,
                fadeSpeed: 200,
                onInit: $.proxy(updateMetadata, $container),
                onSelectChange: $.proxy(updateMetadata, $container),
                instance: true,
                hide: !isVisible
            });
        $container.data('cropbox', cropbox);
        $values.find('.aspectRatio').val(ar);
    });

    // Respect user changes in any of the fields
    $('.typo3-dyntabmenu-divs').delegate('input', 'blur', function() {

        var $field = $(this),
            value = $field.val(),
            behaveAs = $field.parent().attr('data-identity'),
            $panel = $field.closest('.c-tablayer'),
            cropbox = $panel.data('cropbox'),
            selection = cropbox.getSelection(),
            x1 = selection.x1,
            x2 = selection.x2,
            y1 = selection.y1,
            y2 = selection.y2;

        // x1, x2, y1, y2 are valid
        if ('x1' === behaveAs) {
            x1 = parseInt(value);
        }
        if ('x2' === behaveAs) {
            x2 = parseInt(value);
        }
        if ('y1' === behaveAs) {
            y1 = parseInt(value);
        }
        if ('y2' === behaveAs) {
            y2 = parseInt(value);
        }
        if ('width' === behaveAs) {
            x2 = selection.x1 + parseInt(value);
        }
        if ('height' === behaveAs) {
            y2 = selection.y1 + parseInt(value);
        }

        // Aspect ration is updated globally and will only be validated here
        if ('ar' == behaveAs) {
          // Validate first
          if (-1 === value.indexOf(':')) {
              return;
          }
          updateAr(value);
          return;
        }

        cropbox.setSelection(x1, y1, x2, y2);
        cropbox.update();
        updateMetadata.call($panel, null, cropbox.getSelection());
    });

    /**
     * Updates the metadata information
     *
     * @param img
     * @param selection
     */
    function updateMetadata(img, selection) {
      // Set parameters that are part of the current selection
      this.find(".x1").val(selection.x1);
      this.find(".y1").val(selection.y1);
      this.find(".x2").val(selection.x2);
      this.find(".y2").val(selection.y2);
      this.find(".w").val(selection.width);
      this.find(".h").val(selection.height);
    }

    /**
     * Updates the aspect ration globally
     *
     * @param {string} newAr
     * @return {void}
     */
    function updateAr(newAr) {
        $('.typo3-dyntabmenu-divs').children().each(function() {

            var $panel = $(this),
                isVisible = ($panel.filter(':visible').length == 1)
                cropbox = $panel.data('cropbox'),
                selection = cropbox.getSelection(),
                x1 = selection.x1,
                x2 = selection.x2,
                y1 = selection.y1,
                y2 = selection.y2;

            var ratio = newAr.split(':'),
                gcd = parseInt(ratio[0]) / parseInt(ratio[1]);

            cropbox.setOptions({
                aspectRatio : newAr
            });

            if (!isVisible) return;

            y2 = parseInt(y1 + selection.width * gcd);

            cropbox.setSelection(x1, y1, x2, y2);
            cropbox.update();
            updateMetadata.call($panel, null, cropbox.getSelection());
            $panel.find('.aspectRatio').val(newAr);
        });
    }


});