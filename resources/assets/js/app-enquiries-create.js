/**
 * Page Enquiries - Create
 */

'use strict';

$(function () {
  var select2 = $('.select2'),
    flatpickr = $('.flatpickr');

  if (select2.length) {
    select2.each(function () {
      var $this = $(this);
      if ($this.hasClass('select2-hidden-accessible')) {
        $this.select2('destroy');
      }
      $this.wrap('<div class="position-relative"></div>').select2({
        dropdownParent: $this.parent(),
        width: '100%'
      });
    });
  }

  if (flatpickr.length) {
    flatpickr.flatpickr({
      dateFormat: 'Y-m-d',
      defaultDate: 'today'
    });
  }

  // Mobile number masking (Cleave.js)
  const mobileNumber = document.querySelector('#mobile_number');
  if (mobileNumber) {
    new Cleave(mobileNumber, {
      phone: true,
      phoneRegionCode: 'IN'
    });
  }

  const alternateMobile = document.querySelector('#alternate_mobile');
  if (alternateMobile) {
    new Cleave(alternateMobile, {
      phone: true,
      phoneRegionCode: 'IN'
    });
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  var indianMobileRegex = /^[6-9]\d{9}$/;

  const addEnquiryForm = document.getElementById('addEnquiryForm');
  if (addEnquiryForm) {
    const fv = FormValidation.formValidation(addEnquiryForm, {
      fields: {
        enquiry_date: {
          validators: {
            notEmpty: {
              message: 'Please select enquiry date'
            }
          }
        },
        customer_name: {
          validators: {
            notEmpty: {
              message: 'Please enter customer name'
            }
          }
        },
        mobile_number: {
          validators: {
            notEmpty: {
              message: 'Please enter mobile number'
            },
            callback: {
              message: 'Please enter a valid Indian 10-digit mobile number',
              callback: function(input) {
                const phone = input.value.replace(/\D/g, '');
                return indianMobileRegex.test(phone);
              }
            }
          }
        },
        alternate_mobile: {
          validators: {
            callback: {
              message: 'Please enter a valid Indian 10-digit mobile number',
              callback: function(input) {
                if (input.value === '') return true;
                const phone = input.value.replace(/\D/g, '');
                return indianMobileRegex.test(phone);
              }
            }
          }
        },
        enquiry_source_id: {
          validators: {
            notEmpty: {
              message: 'Please select enquiry source'
            }
          }
        },
        pincode: {
          validators: {
            callback: {
              message: 'Pincode must be a valid 6-digit number',
              callback: function (input) {
                if (input.value === '') return true;
                return /^[0-9]{6}$/.test(input.value);
              }
            }
          }
        },
        initial_remark: {
          validators: {
            notEmpty: {
              message: 'Please enter remark'
            }
          }
        },
        lead_type: {
          validators: {
            notEmpty: {
              message: 'Please select lead type'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: 'is-valid',
          eleInvalidClass: 'is-invalid',
          rowSelector: function (field, ele) {
            return '.mb-3';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    }).on('core.form.valid', function () {
      var formData = $('#addEnquiryForm').serialize();

      $.ajax({
        data: formData,
        url: baseUrl + 'enquiries',
        type: 'POST',
        success: function (response) {
          if (response.success) {
            Swal.fire({
              icon: 'success',
              title: 'Successfully created!',
              text: 'Enquiry created successfully.',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            }).then(function () {
              window.location.href = baseUrl + 'enquiries';
            });
          }
        },
        error: function (err) {
          var errorMsg = 'Something went wrong!';
          if (err.responseJSON) {
            if (err.responseJSON.message) {
              errorMsg = err.responseJSON.message;
            } else if (err.responseJSON.errors) {
              var errors = Object.values(err.responseJSON.errors).flat();
              errorMsg = errors.join('<br>');
            }
          }
          Swal.fire({
            title: 'Error!',
            html: errorMsg,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        }
      });
    });
  }
});
