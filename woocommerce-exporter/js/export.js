(function ($, window) {
  // Get params from localized script.
  var $params = wsed_export_params;

  var WSED_QuickExport = (function () {
    /**
     * QuickExport contructor.
     */
    function QuickExport() {
      // Make sure is called as a constructor.
      if (!(this instanceof QuickExport)) {
        return new QuickExport();
      }

      // Holds the jQuery instance.
      this.$el = $('.export_button').closest('form');
      this.$button = this.$el.find('.export_button');
      this.$modal = $('#wsed-export-progress-bar-modal');
      this.$modalCloseBtn = this.$modal.find('.modal-close');
      this.$progressBar = this.$modal.find('.progress-bar');
      this.$progressBarLabel = this.$modal.find('.progress-value');

      // Holds the export running state.
      this.exportRunning = false;

      this.exportNonce = this.$el.find('#woo_ce_export').val();
      this.exportType = this.$el.find('input[name="dataset"]:checked').val();

      // Holds the export status.
      this.exportStatus = '';

      // Holds the export progress values.
      this.total = 0;
      this.exported = 0;
      this.percentage = 0;
      this.batches = 0;

      this.setupEvents();
    }

    /**
     * Setup events.
     */
    QuickExport.prototype.setupEvents = function () {
      var self = this;

      // Controls the Export button on the Export screen.
      self.$button.on('click', function (events) {
        events.preventDefault();

        self.exportRunning = true;
        self.exportStatus = 'preparing';

        self.show();
        self.$progressBarLabel.text($params.i18n.export_preparing);

        // Trigger ajax call
        $.ajax({
          url: ajaxurl,
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'woo_ce_quick_export',
            nonce: self.exportNonce,
            type: self.exportType,
            method: 'prepare',
            form_data: self.$el.serialize(),
          },
          success: function (response) {
            if (response.success) {
              self.exportStatus = 'running';
              self.total = response.data.total;
              self.batches = response.data.batches;

              self.update();

              // Trigger ajax call
              self.exportBatch(1);
            } else {
              self.exportStatus = 'failed';
              self.exportRunning = false;

              self.$modalCloseBtn.show();
              self.$progressBar.progressbar({ value: 100 });
              self.$progressBar.addClass('progress-bar-error');
              self.$progressBarLabel.text(response.data.message);
            }
          },
          error: function (response) {
            self.exportStatus = 'failed';
            self.exportRunning = false;

            self.$modalCloseBtn.show();
            self.$progressBar.progressbar({ value: 100 });
            self.$progressBar.addClass('progress-bar-error');
            self.$progressBarLabel.text(response.data.message);
          },
        });
      });

      // Event to change the export type.
      self.$el.find('input[name="dataset"]').on('change', function () {
        $(this).prop('checked', true);
        self.exportType = $(this).val();
      });

      // Controls the Quick Export button, behaves like the Export button.
      self.$el.find('#quick_export').on('click', function (events) {
        events.preventDefault();
        self.$el.find('#export_' + self.exportType).trigger('click');
      });

      // Prevent the user from closing the page while the export is running.
      window.addEventListener('beforeunload', function (e) {
        if (self.exportRunning) {
          // Cancel the event.
          e.preventDefault();

          // Create the data to send.
          var data = new FormData();
          data.append('action', 'woo_ce_quick_export');
          data.append('nonce', self.exportNonce);
          data.append('type', self.exportType);
          data.append('method', 'clear_export');

          // Send the beacon.
          navigator.sendBeacon(ajaxurl, data);

          // Chrome requires returnValue to be set.
          e.returnValue = '';
        }
      });

      // Initialize progress bar.
      self.$progressBar.progressbar({
        value: false,
      });

      // If click out side of modal then close the modal
      // only if modal shown and if the export process is finished.
      $(document).click(function (e) {
        if ($(e.target).is(self.$modal) && self.$modal.hasClass('show') && !self.exportRunning) {
          self.hide();
        }
      });

      self.$modalCloseBtn.click(function (e) {
        if (!self.exportRunning) {
          self.hide();
          self.reset();
        }
      });
    };

    /**
     * Update progress bar and label.
     */
    QuickExport.prototype.exportBatch = function (step) {
      var self = this;

      // Trigger ajax call
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'woo_ce_quick_export',
          nonce: self.exportNonce,
          type: self.exportType,
          method: 'batch',
          step: step,
        },
        success: function (response) {
          if (response.success) {
            self.exported = response.data.exported;
            self.update();

            if (response.data?.done) {
              self.exportRunning = false;
              self.exportStatus = 'done';

              self.$modalCloseBtn.show();
              self.$progressBar.progressbar({ value: 100 });
              self.$progressBarLabel.text($params.i18n.export_complete);

              // Redirect to download the file.
              window.location.href = response.data.file_url;
            } else {
              self.exportBatch(response.data.step);
            }
          } else {
            self.clearExportTransient();

            self.exportRunning = false;
            self.exportStatus = 'failed';

            self.$modalCloseBtn.show();
            self.$progressBar.progressbar({ value: 100 });
            self.$progressBar.addClass('progress-bar-error');
            self.$progressBarLabel.text(response.data.message);
          }
        },
        error: function (response) {
          self.clearExportTransient();

          self.exportRunning = false;
          self.exportStatus = 'failed';

          self.$modalCloseBtn.show();
          self.$progressBar.progressbar({ value: 100 });
          self.$progressBar.addClass('progress-bar-error');
          self.$progressBarLabel.text(response.data.message);
        },
      });
    };

    /**
     * Update progress bar and label.
     */
    QuickExport.prototype.update = function () {
      var self = this;

      self.percentage = Math.round((self.exported / self.total) * 100);

      self.$progressBar.progressbar({ value: self.percentage });
      self.$progressBarLabel.text(self.exported + '/' + self.total + ' (' + self.percentage + '%)');
    };

    /**
     * Show the modal.
     */
    QuickExport.prototype.show = function () {
      this.$modal.addClass('show');
    };

    /**
     * Hide the modal.
     */
    QuickExport.prototype.hide = function () {
      this.$modal.removeClass('show');
      this.reset();
    };

    /**
     * Reset the modal state and values.
     */
    QuickExport.prototype.reset = function () {
      // Holds the export running state.
      this.exportRunning = false;

      // Holds the export status.
      this.exportStatus = '';

      // Holds the export progress values.
      this.total = 0;
      this.exported = 0;
      this.batch = 0;

      // Hide close button.
      this.$modalCloseBtn.hide();

      // Reset progress bar.
      this.$progressBar.removeClass('progress-bar-error');
      this.$progressBar.progressbar({ value: false });

      // Reset progress label.
      this.$progressBarLabel.text('');
    };

    /**
     * Clear the export transient.
     */
    QuickExport.prototype.clearExportTransient = function () {
      var self = this;

      // Trigger ajax call
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'woo_ce_quick_export',
          nonce: self.exportNonce,
          method: 'clear_export',
          type: self.exportType,
        },
      });
    };

    return QuickExport;
  })();

  /**
   * DOM ready.
   */
  $(function () {
    new WSED_QuickExport();
  });
})(jQuery, window);
