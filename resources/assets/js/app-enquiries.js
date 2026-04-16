/**
 * Page Enquiries - Listing
 */

'use strict';

$(function () {
  var dt_enquiries_table = $('.datatables-enquiries'),
    enquiryModal = $('#enquiryModal'),
    enquiryModalForm = $('#enquiryModalForm'),
    followUpActionModal = $('#followUpActionModal'),
    followUpActionForm = $('#followUpActionForm'),
    select2 = $('.select2'),
    flatpickrElements = $('.flatpickr');

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

  if (flatpickrElements.length) {
    flatpickrElements.flatpickr({
      dateFormat: 'Y-m-d'
    });
  }

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  if (dt_enquiries_table.length) {
    var dt_enquiries = dt_enquiries_table.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: baseUrl + 'enquiries/data',
        data: function (d) {
          d.date_from = $('#filter_date_from').val();
          d.date_to = $('#filter_date_to').val();
          d.source_id = $('#filter_source').val();
          d.assigned_to = $('#filter_assigned').val();
          d.lead_type = $('#filter_lead_type').val();
          d.location = $('#filter_location').val();
          d.pincode = $('#filter_pincode').val();
          d.enquiry_type = $('#filter_enquiry_type').val();
          d.finance_type = $('#filter_finance_type').val();
          d.customer_profession = $('#filter_customer_profession').val();
          d.status = $('#filter_status').val();
          d.view = window.enquiryConfig.viewStatus;
        }
      },
      columns: [
        { data: '' },
        { data: 'id' },
        { data: 'enquiry_date' },
        { data: 'customer_name' },
        { data: 'mobile_number' },
        { data: 'enquiry_source_id' },
        { data: 'assigned_to' },
        { data: 'created_by' },
        { data: 'lead_type' },
        { data: 'status' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function () {
            return '';
          }
        },
        {
          searchable: false,
          orderable: false,
          targets: 1,
          render: function (data, type, full) {
            return `<span>${full.fake_id}</span>`;
          }
        },
        {
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full) {
            return full.action || '';
          }
        }
      ],
      order: [[2, 'desc']],
      dom:
        '<"row"' +
        '<"col-md-2"<"ms-n2"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-6 mb-md-0 mt-n6 mt-md-0"f>>' +
        '>t' +
        '<"row"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      lengthMenu: [10, 20, 50, 100],
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search Enquiry',
        info: 'Displaying _START_ to _END_ of _TOTAL_ entries',
        paginate: {
          next: '<i class="ti ti-chevron-right ti-sm"></i>',
          previous: '<i class="ti ti-chevron-left ti-sm"></i>'
        }
      },
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['customer_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var $tbody = $('<tbody/>');
            $.each(columns, function (i, col) {
              if (col.title === '') return;
              var $tr = $('<tr/>').attr({ 'data-dt-row': col.rowIndex, 'data-dt-column': col.columnIndex });
              $tr.append($('<td/>').text(col.title + ':'));
              var $td = $('<td/>');
              $td.html(col.data);
              $tr.append($td);
              $tbody.append($tr);
            });
            return $tbody.children().length ? $('<table class="table"/>').append($tbody) : false;
          }
        }
      }
    });
  }

  function initModalPlugins() {
    $('.modal-select2').each(function () {
      var $this = $(this);
      if ($this.hasClass('select2-hidden-accessible')) {
        $this.select2('destroy');
      }
      $this.select2({
        dropdownParent: enquiryModal,
        width: '100%'
      });
    });

    $('.flatpickr-modal').each(function () {
      if (this._flatpickr) {
        this._flatpickr.destroy();
      }
      var pickerOptions = {
        altInput: true,
        altFormat: 'd-m-Y',
        dateFormat: 'Y-m-d'
      };

      this._flatpickr = flatpickrFactory(this, pickerOptions);
    });

    if (!window.modalMobileCleave) {
      window.modalMobileCleave = new Cleave('#modal_mobile_number', {
        phone: true,
        phoneRegionCode: 'IN'
      });
    }
    if (!window.modalAltMobileCleave) {
      window.modalAltMobileCleave = new Cleave('#modal_alternate_mobile', {
        phone: true,
        phoneRegionCode: 'IN'
      });
    }

    // Hard cap input lengths for numeric text fields.
    $('#modal_mobile_number, #modal_alternate_mobile').off('input.maxDigits').on('input.maxDigits', function () {
      const digits = $(this).val().replace(/\D/g, '').slice(0, 10);
      $(this).val(digits);
    });

    $('#modal_pincode').off('input.maxDigits').on('input.maxDigits', function () {
      const digits = $(this).val().replace(/\D/g, '').slice(0, 6);
      $(this).val(digits);
    });
  }

  function flatpickrFactory(element, options) {
    return window.flatpickr(element, options);
  }

  function resetEnquiryModalForm() {
    enquiryModalForm[0].reset();
    if ($('#modal_enquiry_date')[0]?._flatpickr) {
      $('#modal_enquiry_date')[0]._flatpickr.clear();
    }
    if ($('#modal_next_follow_up_date')[0]?._flatpickr) {
      $('#modal_next_follow_up_date')[0]._flatpickr.clear();
    }
    $('#modal_enquiry_id').val('');
    $('#modal_status').val('Pending');
    $('.modal-select2').val(null).trigger('change');
    $('#modal_status').val('Pending').trigger('change');
    $('#enableEditBtn').addClass('d-none');
    $('#enquiryModalSubmitBtn').removeClass('d-none');
    $('#followupHistorySection').addClass('d-none');
    $('#followupHistoryBody').html(
      '<tr><td colspan="7" class="text-center text-muted">No follow-up history available.</td></tr>'
    );
  }

  function renderFollowupHistory(followUps) {
    if (!followUps || !followUps.length) {
      $('#followupHistoryBody').html(
        '<tr><td colspan="7" class="text-center text-muted">No follow-up history available.</td></tr>'
      );
      return;
    }

    const rows = followUps
      .map(function (item) {
        return `<tr>
          <td>${item.follow_up_date || '-'}</td>
          <td>${item.previous_status || '-'}</td>
          <td>${item.new_status || '-'}</td>
          <td>${item.next_follow_up_date || '-'}</td>
          <td>${item.remark || '-'}</td>
          <td>${item.created_by || '-'}</td>
          <td>${item.created_at || '-'}</td>
        </tr>`;
      })
      .join('');

    $('#followupHistoryBody').html(rows);
  }

  function setEnquiryFormEditable(isEditable) {
    $('#enquiryModalForm')
      .find('input, textarea, select')
      .not('#modal_enquiry_id')
      .prop('disabled', !isEditable);

    $('#enquiryModalSubmitBtn').toggleClass('d-none', !isEditable);
  }

  function openCreateEnquiryModal() {
    $('#enquiryModalTitle').text('Add New Enquiry');
    $('#enquiryModalSubmitBtn').text('Save Enquiry');
    resetEnquiryModalForm();
    if ($('#modal_enquiry_date')[0]?._flatpickr) {
      $('#modal_enquiry_date')[0]._flatpickr.setDate(new Date(), true, 'Y-m-d');
    } else {
      $('#modal_enquiry_date').val(new Date().toISOString().slice(0, 10));
    }
    setEnquiryFormEditable(true);
    enquiryModal.modal('show');
  }

  function fillEnquiryModal(data) {
    $('#modal_enquiry_id').val(data.id || '');
    if ($('#modal_enquiry_date')[0]?._flatpickr) {
      $('#modal_enquiry_date')[0]._flatpickr.setDate(data.enquiry_date || null, true, 'Y-m-d');
    } else {
      $('#modal_enquiry_date').val(data.enquiry_date || '');
    }
    $('#modal_customer_name').val(data.customer_name || '');
    $('#modal_mobile_number').val(data.mobile_number || '');
    $('#modal_alternate_mobile').val(data.alternate_mobile || '');
    $('#modal_email').val(data.email || '');
    $('#modal_location').val(data.location || '');
    $('#modal_pincode').val(data.pincode || '');
    $('#modal_product_service').val(data.product_service || '');
    $('#modal_initial_remark').val(data.initial_remark || '');
    if ($('#modal_next_follow_up_date')[0]?._flatpickr) {
      $('#modal_next_follow_up_date')[0]._flatpickr.setDate(data.next_follow_up_date || null, true, 'Y-m-d');
    } else {
      $('#modal_next_follow_up_date').val(data.next_follow_up_date || '');
    }
    $('#modal_capacity_kw').val(data.capacity_kw || '');
    $('#modal_shadow_free_area_sqft').val(data.shadow_free_area_sqft || '');
    $('#modal_consumer_number').val(data.consumer_number || '');
    $('#modal_enquiry_type').val(data.enquiry_type || '').trigger('change');
    $('#modal_enquiry_source_id').val(data.enquiry_source_id || '').trigger('change');
    $('#modal_assigned_to').val(data.assigned_to || '').trigger('change');
    $('#modal_lead_type').val(data.lead_type || '').trigger('change');
    $('#modal_status').val(data.status || 'Pending').trigger('change');
    $('#modal_finance_type').val(data.finance_type || '').trigger('change');
    $('#modal_customer_profession').val(data.customer_profession || '').trigger('change');
  }

  function toggleNextFollowUpDateField() {
    const isPendingStatus = $('#modal_status').val() === 'Pending';
    const nextDateFieldWrapper = $('#nextFollowUpDateWrapper');
    const nextDateInput = $('#modal_next_follow_up_date');
    const requiredMark = $('#nextFollowUpRequiredMark');

    nextDateFieldWrapper.toggleClass('d-none', !isPendingStatus);
    requiredMark.toggleClass('d-none', !isPendingStatus);
    nextDateInput.prop('disabled', !isPendingStatus);

    if (!isPendingStatus) {
      const flatpickrInstance = nextDateInput[0]?._flatpickr;
      if (flatpickrInstance) {
        flatpickrInstance.clear();
      } else {
        nextDateInput.val('');
      }
    }
  }

  function redrawEnquiryTable() {
    if (dt_enquiries) {
      dt_enquiries.draw();
    }
  }

  $('#collapseFilter').on('show.bs.collapse', function () {
    $('[data-bs-target="#collapseFilter"]').find('i.ti').last().removeClass('ti-chevron-down').addClass('ti-chevron-up');
  }).on('hide.bs.collapse', function () {
    $('[data-bs-target="#collapseFilter"]').find('i.ti').last().removeClass('ti-chevron-up').addClass('ti-chevron-down');
  });

  $('#resetFilters').on('click', function () {
    $('#filterForm')[0].reset();
    $('.select2').val(null).trigger('change');
    $('.flatpickr').val('');
    redrawEnquiryTable();
  });

  // Auto-apply filters on any change.
  $('#filterForm').on('change', 'select, input', function () {
    redrawEnquiryTable();
  });

  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);

  initModalPlugins();

  $('#openAddEnquiryModal').on('click', function () {
    openCreateEnquiryModal();
  });

  function openExistingEnquiryModal(enquiry_id, editableByDefault, canEdit) {
    var dtrModal = $('.dtr-bs-modal.show');

    if (dtrModal.length) {
      dtrModal.modal('hide');
    }

    $.get(`${baseUrl}enquiries/${enquiry_id}/edit`, function (response) {
      if (response.success) {
        var data = response.data.enquiry;
        var followUps = response.data.followUps || [];
        $('#enquiryModalTitle').text('Enquiry Details');
        $('#enquiryModalSubmitBtn').text('Update Enquiry');
        resetEnquiryModalForm();
        fillEnquiryModal(data);
        setEnquiryFormEditable(editableByDefault);

        if (!editableByDefault) {
          renderFollowupHistory(followUps);
          $('#followupHistorySection').removeClass('d-none');
        }

        if (!editableByDefault && canEdit) {
          $('#enableEditBtn').removeClass('d-none');
        }

        enquiryModal.modal('show');
      }
    }).fail(function (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.responseJSON?.message || 'Failed to load enquiry data',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
    });
  }

  function openEnquiryFromQueryParam() {
    const params = new URLSearchParams(window.location.search);
    const enquiryId = params.get('open_enquiry');
    const canEdit = params.get('open_enquiry_can_edit') === '1';

    if (!enquiryId) {
      return;
    }

    openExistingEnquiryModal(enquiryId, false, canEdit);
    params.delete('open_enquiry');
    params.delete('open_enquiry_can_edit');
    const updatedQuery = params.toString();
    const updatedUrl = `${window.location.pathname}${updatedQuery ? `?${updatedQuery}` : ''}`;
    window.history.replaceState({}, '', updatedUrl);
  }

  $(document).on('click', '.view-record', function () {
    var enquiry_id = $(this).data('id');
    var canEdit = $(this).data('can-edit') == 1;
    openExistingEnquiryModal(enquiry_id, false, canEdit);
  });

  openEnquiryFromQueryParam();

  $('#enableEditBtn').on('click', function () {
    setEnquiryFormEditable(true);
    $('#enableEditBtn').addClass('d-none');
    $('#enquiryModalTitle').text('Edit Enquiry');
    $('#followupHistorySection').addClass('d-none');
  });

  var indianMobileRegex = /^[6-9]\d{9}$/;
  var enquiryFormValidator = null;
  var isSubmittingEnquiryForm = false;

  const modalForm = document.getElementById('enquiryModalForm');
  if (modalForm) {
    function submitEnquiryForm() {
      if (isSubmittingEnquiryForm) {
        return;
      }

      var enquiry_id = $('#modal_enquiry_id').val();
      var isEdit = !!enquiry_id;
      var formData = $('#enquiryModalForm').serialize();

      if (isEdit) {
        formData += '&_method=PUT';
      }

      isSubmittingEnquiryForm = true;
      $('#enquiryModalSubmitBtn').prop('disabled', true);

      $.ajax({
        data: formData,
        url: isEdit ? `${baseUrl}enquiries/${enquiry_id}` : `${baseUrl}enquiries`,
        type: 'POST',
        success: function () {
          if (dt_enquiries) {
            // After creating a new enquiry, clear any active datatable search/filter
            // so the user sees the fresh row immediately.
            if (!isEdit) {
              dt_enquiries.search('');
              $('.dataTables_filter input').val('');
            }
            dt_enquiries.ajax.reload(null, false);
          }
          enquiryModal.modal('hide');
          Swal.fire({
            icon: 'success',
            title: isEdit ? 'Successfully updated!' : 'Successfully created!',
            text: isEdit ? 'Enquiry updated successfully.' : 'Enquiry created successfully.',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        },
        error: function (err) {
          var errorMsg = err.responseJSON?.message || 'Something went wrong!';
          if (err.responseJSON?.errors) {
            errorMsg = Object.values(err.responseJSON.errors).flat().join('\n');
          }
          Swal.fire({
            title: 'Error!',
            text: errorMsg,
            icon: 'error',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });
        },
        complete: function () {
          isSubmittingEnquiryForm = false;
          $('#enquiryModalSubmitBtn').prop('disabled', false);
        }
      });
    }

    enquiryFormValidator = FormValidation.formValidation(modalForm, {
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
            },
            stringLength: {
              min: 2,
              max: 255,
              message: 'Customer name must be between 2 and 255 characters'
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
              callback: function (input) {
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
              callback: function (input) {
                if (input.value === '') return true;
                const phone = input.value.replace(/\D/g, '');
                return indianMobileRegex.test(phone);
              }
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
        assigned_to: {
          validators: {
            notEmpty: {
              message: 'Please select assigned user'
            }
          }
        },
        enquiry_source_id: {
          validators: {
            notEmpty: {
              message: 'Please select source'
            }
          }
        },
        initial_remark: {
          validators: {
            notEmpty: {
              message: 'Please enter remark'
            },
            stringLength: {
              min: 3,
              message: 'Remark must be at least 3 characters'
            }
          }
        },
        capacity_kw: {
          validators: {
            callback: {
              message: 'Capacity must be 0 or greater',
              callback: function (input) {
                if (input.value === '') return true;
                return !isNaN(input.value) && parseFloat(input.value) >= 0;
              }
            }
          }
        },
        shadow_free_area_sqft: {
          validators: {
            callback: {
              message: 'Shadow free area must be 0 or greater',
              callback: function (input) {
                if (input.value === '') return true;
                return !isNaN(input.value) && parseFloat(input.value) >= 0;
              }
            }
          }
        },
        lead_type: {
          validators: {
            notEmpty: {
              message: 'Please select lead type'
            }
          }
        },
        status: {
          validators: {
            notEmpty: {
              message: 'Please select status'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: function (field, ele) {
            return '.mb-3';
          }
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      }
    });

    $('#modal_status').on('change', function () {
      toggleNextFollowUpDateField();

      if (!enquiryFormValidator) {
        return;
      }

      if ($(this).val() !== 'Pending') {
        $('#modal_next_follow_up_date').val('');
      }
    });

    toggleNextFollowUpDateField();

    $('#enquiryModalForm').on('submit', function (e) {
      e.preventDefault();

      const status = $('#modal_status').val();
      const nextFollowUpDate = $('#modal_next_follow_up_date').val();
      const enquiryDate = $('#modal_enquiry_date').val();

      if (status !== 'Pending') {
        $('#modal_next_follow_up_date').val('');
      }

      if (status === 'Pending' && !nextFollowUpDate) {
        Swal.fire({
          title: 'Validation Error',
          text: 'Please select next follow-up date',
          icon: 'warning',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
        return;
      }

      if (status === 'Pending' && nextFollowUpDate && enquiryDate && nextFollowUpDate < enquiryDate) {
        Swal.fire({
          title: 'Validation Error',
          text: 'Next follow-up date must be same or after enquiry date',
          icon: 'warning',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
        return;
      }

      enquiryFormValidator.validate().then(function (status) {
        if (status === 'Valid') {
          submitEnquiryForm();
        }
      });
    });
  }

  enquiryModal.on('hidden.bs.modal', function () {
    resetEnquiryModalForm();
  });

  $(document).on('click', '.delete-record', function () {
    var enquiry_id = $(this).data('id');
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $.ajax({
          type: 'DELETE',
          url: `${baseUrl}enquiries/${enquiry_id}`,
          success: function (response) {
            dt_enquiries.draw();
            Swal.fire({
              icon: 'success',
              title: 'Deleted!',
              text: 'Enquiry has been deleted.',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          },
          error: function (err) {
            Swal.fire({
              title: 'Error!',
              text: 'Something went wrong!',
              icon: 'error',
              customClass: {
                confirmButton: 'btn btn-success'
              }
            });
          }
        });
      }
    });
  });

  $(document).on('click', '.update-status', function () {
    var enquiry_id = $(this).data('id');
    var status = $(this).data('status');
    var actionText = status === 'Accepted' ? 'Accept' : 'Cancel';
    $('#follow_up_enquiry_id').val(enquiry_id);
    $('#follow_up_status').val(status);
    $('#follow_up_status_label').val(actionText);
    $('#follow_up_remark').val('');
    followUpActionModal.modal('show');
  });

  followUpActionForm.on('submit', function (e) {
    e.preventDefault();
    var enquiry_id = $('#follow_up_enquiry_id').val();
    var status = $('#follow_up_status').val();
    var remark = $('#follow_up_remark').val().trim();

    if (!remark) {
      Swal.fire({
        title: 'Validation Error',
        text: 'Follow-up remark is required.',
        icon: 'warning',
        customClass: {
          confirmButton: 'btn btn-success'
        }
      });
      return;
    }

    $.ajax({
      type: 'PATCH',
      url: `${baseUrl}enquiries/${enquiry_id}/status`,
      data: { status: status, follow_up_remark: remark },
      success: function () {
        followUpActionModal.modal('hide');
        dt_enquiries.draw();
        Swal.fire({
          icon: 'success',
          title: 'Status Updated!',
          text: 'Enquiry status has been updated to ' + status,
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      },
      error: function (err) {
        Swal.fire({
          title: 'Error!',
          text: err.responseJSON?.message || 'Something went wrong!',
          icon: 'error',
          customClass: {
            confirmButton: 'btn btn-success'
          }
        });
      }
    });
  });
});
