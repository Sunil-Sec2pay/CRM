<?php include 'header.php';?>
<style>
  .card-header-tabs {
    margin: 0% !important;
  }

  /* .swal2-popup {
     width: 250px; 
    padding: 0px;
    font-size: 10px;
  } */

  #createNoteSvg {
    position: fixed;
    z-index: 1050;
    background: white;
    border-radius: 50%;
  }

  .small-note-modal {
    position: fixed;
    right: 125px;
    top: 200px;
    background: white;
    /* padding: 15px; */
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.3);
    width: 250px;
  }

  .nav-link {
    color: black;
    background-color: transparent;
  }
</style>
<div class="page-wrapper">
  <div class="page-header d-print-none">
    <div class="container-xl">
      <div class="row g-2 align-items-center">
        <div class="col">
          <h2 class="page-title">
            Leads
          </h2>
        </div>
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Leads List</h3>
            </div>
            <div class="card-body border-bottom py-3">
              <!-- Edited : Abhishek Chandane -->
              <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
                <table class="table card-table table-vcenter text-nowrap datatable">
                  <thead>
                    <tr>
                      <th class="w-1 sticky-col">View Lead</th>
                      <th class="w-1 sticky-col">Sr.No</th>
                      <th class="w-1 sticky-col">LAST MODIFIED</th>
                      <th class="w-1 sticky-col">CREATED AT</th>
                      <th>LEAD NAME</th>
                      <th>LEAD OWNER</th>
                      <th>QUICK ACTION</th>
                      <th>SALES PERSON</th>
                      <th>EMPLOYEE ID</th>
                      <th>SUBJECT</th>
                      <th>LEAD SOURCE</th>
                      <th>LEAD VALUE</th>
                      <th>LEAD TYPE</th>
                      <th>CONTACT PERSON</th>
                      <th>STAGE</th>
                      <th>ACTIONS</th>
                    </tr>
                  </thead>
                  <tbody id="leadTableBody">
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal modal-blur fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Lead Details</h5>
        <button type="button" class="btn-close btn-danger" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="card" style="height:450px;">
          <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs" role="tablist">
              <li class="nav-item" role="presentation">
                <a href="#tabs-home-8" class="nav-link active" data-bs-toggle="tab" aria-selected="true" role="tab">Lead
                  Data</a>
              </li>
              <li class="nav-item" role="presentation">
                <a href="#tabs-profile-8" class="nav-link" data-bs-toggle="tab" aria-selected="false" tabindex="-1"
                  role="tab">Lead Follow up</a>
              </li>
              <li class="nav-item" role="presentation">
                <a href="#tabs-lead-status-8" class="nav-link" data-bs-toggle="tab" aria-selected="false" tabindex="-1"
                  role="tab">Lead Status</a>
              </li>
              <li class="nav-item" role="presentation">
                <a href="#tabs-activity-8" class="nav-link" data-bs-toggle="tab" aria-selected="false" tabindex="-1"
                  role="tab">Notes</a>
              </li>
              <li class="nav-item ms-auto" role="presentation">
                <a href="#tabs-assign-users-8" class="nav-link" title="Settings" data-bs-toggle="tab"
                  aria-selected="false" tabindex="-1" role="tab"><i class="fa fa-user-plus" title="Assign User"></i>
                </a>
              </li>
            </ul>
          </div>
          <div class="card-body content-scrollable">
            <div class="tab-content ">
              <div class="tab-pane active show" id="tabs-home-8" role="tabpanel">
                <div class="row">
                  <div id="leadDetailsList"></div>
                </div>
              </div>
              <div class="tab-pane" id="tabs-profile-8" role="tabpanel">
                <div class="d-flex align-items-start">
                  <div class="nav flex-column nav-pills me-3 shadow-sm bg-white rounded" id="followUpNav" role="tablist"
                    aria-orientation="vertical"></div>
                  <div class="tab-content w-100" id="followUpTabContent"></div>
                </div>
              </div>
              <div class="tab-pane" id="tabs-lead-status-8" role="tabpanel">
                <div class="d-flex align-items-start">
                  <form id="lead_status_form">
                    <select name="lead_status" id="lead_status" class="selectpicker">
                    </select>
                    <div id="datepicker-container" style="display:none;">
                      <input type="date" name="demo_scheduled_date" id="demo_scheduled_date" class="form-cotrol">
                    </div>
                    <input type="submit" class="btn btn-success btn-sm" name="submit" id="submit" value="Submit">
                  </form>
                </div>
              </div>
              <div class="tab-pane" id="tabs-activity-8" role="tabpanel">
                <div class="d-flex justify-content-end mb-3 position-relative">
                  <svg title="add note" id="createNoteSvg" xmlns="http://www.w3.org/2000/svg"
                    class="mail-icon text-dark" width="25" height="25" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    style="cursor: pointer;">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" />
                    <path d="M13.5 6.5l4 4" />
                  </svg>
                  <div id="smallNoteModal" class="small-note-modal d-none">
                    <div class="modal-content p-3">
                      <form id="smallNoteForm">
                        <div class="mb-2">
                          <label for="noteInput" class="form-label">Create Note</label>
                          <textarea class="form-control" id="noteInput" placeholder="Enter note" rows="3"
                            required></textarea>
                        </div>
                        <div class="d-flex" style="justify-content:end">
                          <button type="submit" class="btn btn-6 btn-outline-success"><i
                              class="fa-brands fa-telegram"></i></button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                <div class="note-list"></div>
              </div>
              <div class="tab-pane" id="tabs-assign-users-8" role="tabpanel">
                <form id="leadAssignUserForm">
                  <div class="row">
                    <div class="col-lg-6">
                      <div class="form-group mb-2">
                        <select class="selectpicker form-control" data-live-search="true" data-actions-box="true"
                          data-size="5" multiple name="user_id[]" id="user_id" placeholder="Select Users">
                        </select>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <input type="hidden" name="action" id="action" value="leadsmanager.setLeadAssignToUsers">
                      <input type="submit" class="btn btn-submit" name="submit" id="submit" value="Submit">
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <input type="hidden" name="leadId" id="leadId" />
        <!-- <button type="button" class="btn me-auto" data-bs-dismiss="modal">Close</button> -->
        <button type="button" class="btn btn-danger " data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<?php include 'footer.php';?>

<script>
  $(document).ready(function () {
    $('.selectpicker').selectpicker();
    $(".nav-link").on("click", function () {
      let tab = $(this).attr("href");
      if (tab == "#tabs-profile-8") {
        renderFollowUpData();
      }
      if (tab == "#tabs-activity-8") {
        const lead_id = $('#leadId').val();
        getLeadNotes(lead_id);
      }
      if (tab == "#tabs-assign-users-8") {
        const lead_id = $('#leadId').val();
        loadUsers(lead_id);
      }
      if (tab == "#tabs-lead-status-8") {
        const lead_id = $('#leadId').val();
        getLeadStatus(lead_id);
        // get(lead_id);
      }
    });

    function renderFollowUpData() {
      const lead_id = $('#leadId').val();
      const navContainer = document.getElementById("followUpNav");
      const tabContentContainer = document.getElementById("followUpTabContent");

      $.ajax({
        url: "api.php",
        type: "POST",
        dataType: "json",
        data: {
          action: "leadsmanager.getLeadsDetails",
          lead_id: lead_id
        },
        success: function (response) {
          console.log('Full API Response:', response);
          const followUpData = response.leadsDetail[0];
          const columns = [{
              date: "FIRST_FOLLOWUP_DATE",
              remark: "FIRST_FOLLOWUP_REMARK",
              title: "Follow Up 1"
            },
            {
              date: "SECOND_FOLLOWUP_DATE",
              remark: "SECOND_FOLLOWUP_REMARK",
              title: "Follow Up 2"
            },
            {
              date: "THREE_FOLLOWUP_DATE",
              remark: "THREE_FOLLOWUP_REMARK",
              title: "Follow Up 3"
            },
            {
              date: "FOUR_FOLLOWUP_DATE",
              remark: "FOUR_FOLLOWUP_REMARK",
              title: "Follow Up 4"
            },
            {
              date: "FIVE_FOLLOWUP_DATE",
              remark: "FIVE_FOLLOWUP_REMARK",
              title: "Follow Up 5"
            }
          ];
          navContainer.innerHTML = "";
          tabContentContainer.innerHTML = "";
          columns.forEach((column, i) => {
            const followUpDate = followUpData[column.date];
            const followUpRemark = followUpData[column.remark];
            let isReadonly = followUpDate && followUpRemark;
            // Create tab button
            const tabButton = document.createElement("button");
            tabButton.className = `nav-link ${i === 0 ? 'active' : ''}`;
            tabButton.id = `followUpTab${i + 1}`;
            tabButton.setAttribute("data-bs-toggle", "pill");
            tabButton.setAttribute("data-bs-target", `#followUpContent${i + 1}`);
            tabButton.setAttribute("type", "button");
            tabButton.setAttribute("role", "tab");
            tabButton.setAttribute("aria-controls", `followUpContent${i + 1}`);
            tabButton.setAttribute("aria-selected", i === 0 ? "true" : "false");
            tabButton.style.width = "200px";
            tabButton.innerText = column.title;
            tabButton.style.color = "black";
            tabButton.style.backgroundColor = "transparent";
            tabButton.style.border = "1px solid transparent";
            if (i === 0) {
              tabButton.style.border = "1px solid #999696";
              // tabButton.style.borderTop = "1px solid #999696";
              // tabButton.style.borderLeft = "1px solid #999696";
              // tabButton.style.borderBottom = "1px solid #999696";
              // tabButton.style.borderRight = "1px solid #999696";
              // tabButton.style.borderRight = "none";
              tabButton.style.border = "1px solid #999696";
            }
            navContainer.appendChild(tabButton);
            const tabPane = document.createElement("div");
            tabPane.className = `tab-pane fade ${i === 0 ? 'show active' : ''}`;
            tabPane.id = `followUpContent${i + 1}`;
            tabPane.setAttribute("role", "tabpanel");
            tabPane.setAttribute("aria-labelledby", `followUpTab${i + 1}`);
            tabPane.innerHTML = `
                    <div class="card shadow-sm bg-white rounded ${i === 0 ? 'active-tab-content' : ''}">
                        <h5 class="card-header">${column.title}</h5>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div>
                                        <input type="date" id="date${i + 1}" class="form-control" value="${followUpDate || ''}" ${isReadonly ? 'readonly' : ''}>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div>
                                        <textarea id="notes${i + 1}" class="form-control" placeholder="Type something…" rows="2" ${isReadonly ? 'readonly' : ''}>${followUpRemark || ''}</textarea>
                                    </div>
                                </div>
                                <div class="col-lg-4 d-flex justify-content-center">
                                    <div class="mt-3">
                                        <button class="btn btn-outline-success w-100 submitFollowUp" id="submitBtn${i + 1}" data-index="${i + 1}" ${isReadonly ? 'style="display: none;"' : ''}>Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            tabContentContainer.appendChild(tabPane);
          });
          $(document).on("shown.bs.tab", 'button[data-bs-toggle="pill"]', function () {
            $(".nav-link").css({
              "color": "black",
              "background-color": "transparent",
              "border": "1px solid transparent",
              "border-right": "none",
            });
            $(".tab-pane").removeClass("active-tab-content");
            $(this).css({
              "color": "black",
              "background-color": "transparent",
              "border": "1px solid #999696",
            });
            const targetTabContentId = $(this).attr("data-bs-target");
            $(targetTabContentId).addClass("active-tab-content");
          });
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error: ", error);
          alert("Failed to retrieve follow-up data. Please try again.");
        }
      });
    }
    fetchLeads();
    document.addEventListener('DOMContentLoaded', function () {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
    $('.datatable').DataTable({
      scrollX: true,
      paging: true,
      searching: true,
      ordering: true,
      lengthChange: true,
      pageLength: 10,
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search leads...",
      },
      columnDefs: [{
        orderable: false,
        targets: [0, 6, 15]
      }, ],
    });
    $('.callNowModal').on('click', function () {
      var leadId = $(this).data('lead-id');
      console.log('Call Now clicked for Lead ID:', leadId);
      $('#callNowModal #callNowLeadId').text(leadId);
    });
    $('.sendSmsModal').on('click', function () {
      var leadId = $(this).data('lead-id');
      console.log('Send SMS clicked for Lead ID:', leadId);
      $('#sendSmsModal #sendSmsLeadId').text(leadId);
    });
    $('.meetingModal').on('click', function () {
      var leadId = $(this).data('lead-id');
      console.log('Meeting clicked for Lead ID:', leadId);
      $('#meetingModal #meetingLeadId').text(leadId);
    });
    $('.deleteLead').on('click', function (e) {
      e.preventDefault();
      var leadId = $(this).data('lead-id');
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: "Deleted!",
            text: "Your file has been deleted.",
            icon: "success"
          });
        }
      });
    });

    function fetchLeads() {
      $.ajax({
        url: "api.php",
        type: "POST",
        dataType: "json",
        data: {
          action: 'leadsmanager.getLeadsRocords'
        },
        success: function (response) {
          if (response.status === "success" && Array.isArray(response.data)) {
            let rows = "";
            response.data.forEach((lead, index) => {
              rows += `
                              <tr>
                                  <td class="text-center">
                                      <a href="sec2pay_view_lead.html?id=${lead.ID}" class="text-primary" title="View Lead">
                                          <i class="fas fa-eye"></i>
                                      </a>
                                  </td>
                                  <td><span class="text-secondary">${index + 1}</span></td>
                                  <td><span>${lead.CREATED_AT}</span></td>
                                  <td><span>${lead.CREATED_AT}</span></td> <!-- Adjust this as necessary -->
                                  <td data-bs-toggle="tooltip" data-bs-html="true" title="
                                      <b>Lead Name:</b> ${lead.PRIMARY_NAME}<br>
                                      <b>Contact:</b> ${lead.PRIMARY_PHONE}<br>
                                      <b>Owner:</b> ${lead.ORG_OWNER_ID}<br>
                                      <b>Source:</b> ${lead.SOURCE}<br>
                                      <b>Value:</b> ₹${lead.SCORE}">
                                      ${lead.PRIMARY_NAME}
                                  </td>
                                  <td>${lead.ORG_OWNER_ID}</td>
                                  <td>
                                      <div class="dropdown">
                                          <button class="btn quick-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                              <i class="fas fa-bolt"></i>
                                          </button>
                                          <ul class="dropdown-menu quick-action-dropdown">
                                              <li><a class="dropdown-item callNowModal" data-bs-toggle="modal" data-lead-id="${lead.ID}"><i class="fas fa-phone"></i> Call Now</a></li>
                                              <li><a class="dropdown-item sendSmsModal" data-bs-toggle="modal" data-lead-id="${lead.ID}"><i class="fas fa-sms"></i> Send SMS</a></li>
                                              <li><a class="dropdown-item meetingModal" data-bs-toggle="modal" data-lead-id="${lead.ID}"><i class="fas fa-calendar-alt"></i> Meeting</a></li>
                                              <li><a class="dropdown-item editmodal"data-lead-id="${lead.ID}"><i class="fas fa-edit"></i> Edit</a></li>
                                              <li><a class="dropdown-item deleteLead" href="#" data-lead-id="${lead.ID}"><i class="fas fa-trash"></i> Delete</a></li>
                                          </ul>
                                      </div>
                                  </td>
                                  <td><a href="invoice.html?id=${lead.SOURCE}" class="text-reset">${lead.SOURCE}</a></td>
                                  <td>${lead.ORG_OWNER_ID}</td>
                                  <td>${lead.PRODUCT}</td>
                                  <td><i class="fas fa-envelope"></i> ${lead.SOURCE}</td>
                                  <td>₹${lead.SCORE}</td>
                                  <td>${lead.STATUS}</td>
                                  <td>${lead.PRIMARY_PHONE}</td>
                                  <td>${lead.STATUS}</td>
                                  <td class="text-end">
                                      <span class="dropdown">
                                          <button class="btn dropdown-toggle align-text-top" data-bs-toggle="dropdown">Actions</button>
                                          <div class="dropdown-menu dropdown-menu-end">
                                              <a class="dropdown-item" href="#">Action</a>
                                              <a class="dropdown-item" href="#">Another action</a>
                                          </div>
                                      </span>
                                  </td>
                              </tr>
                          `;
            });
            $("#leadTableBody").html(rows);
            $('[data-bs-toggle="tooltip"]').tooltip();
          } else {
            console.log("No leads found or error in API response.");
          }
        },
        error: function () {
          console.log("Error fetching data.");
        }
      });
    }

    $(document).on("submit", "#lead_status_form", function (e) {
      e.preventDefault();
      var lead_status = $('#lead_status').val();
      var leadId = $('#leadId').val();
      if (lead_status === "DEMO SCHEDULED") {
        Swal.fire({
          title: "Select a Date",
          input: "date",
          showCancelButton: true,
          confirmButtonText: "Submit",
          allowOutsideClick: false,
          preConfirm: (date) => {
            if (!date) {
              Swal.showValidationMessage("⚠ Please select a date!");
            }
            return date;
          }
        }).then((result) => {
          if (result.isConfirmed && result.value) {
            $.ajax({
              url: "api.php",
              type: "POST",
              data: {
                lead_id: leadId,
                new_lead_status: lead_status,
                schedule_date: result.value,
                action: "leadsmanager.leadStatusUpdate",
              },
              dataType: "json",
              beforeSend: function () {
                Swal.fire({
                  title: "Processing...",
                  text: "Please wait...",
                  allowOutsideClick: false,
                  didOpen: () => {
                    Swal.showLoading();
                  }
                });
              },
              success: function (response) {
                console.log("Success Response:1", response);
                if (response.status == "success") {
                  Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: "Date saved successfully!"
                  });
                } else {
                  Swal.fire({
                    icon: "error",
                    title: "error",
                    text: response.message
                  });
                }
              },
              error: function (xhr, status, error) {
                Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: "Failed to save date!"
                });
                console.error("Error:", error);
              }
            });
          } else {
            $("#lead_status").val("");
          }
        });
      } else {
        $.ajax({
          url: "api.php",
          type: "POST",
          data: {
            lead_id: leadId,
            new_lead_status: lead_status,
            action: "leadsmanager.leadStatusUpdate",
          },
          dataType: "json",
          beforeSend: function () {
            Swal.fire({
              title: "Processing...",
              text: "Please wait...",
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
          },
          success: function (response) {
            console.log("Success Response:2", response);
            if (response.status == "success") {
              Swal.fire({
                icon: "success",
                title: "Success",
                text: response.message
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "error",
                text: response.message
              });
            }
          },
          error: function (xhr, status, error) {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Failed to update status!"
            });
            console.error("Error:", error);
          }
        });
      }
    });
    $(document).on("submit", "#smallNoteForm", function (e) {
      e.preventDefault();
      let noteInput = $('#noteInput').val();
      let leadId = $('#leadId').val();
      let regex = /^[a-zA-Z0-9 ]+$/;

      let formData = {
        leadId: leadId,
        noteInput: noteInput,
        action: "leadsmanager.setLeadNote",
      };
      $.ajax({
        url: "api.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          if (response && response.status == 'success') {
            $('#noteInput').val('')
            getLeadNotes(leadId);
            Swal.fire({
              title: response.message,
              icon: "success",
              draggable: true,
            }).then(() => {
              $("#smallNoteModal").addClass("d-none"); // Close modal after success
            });
          } else {
            Swal.fire({
              title: response.message,
              icon: "error",
              draggable: true
            });
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error: ", error);
          alert("Failed to submit a form. Please try again.");
        }
      });
    })
    $(document).on("click", ".submitFollowUp", function () {
      let index = $(this).data("index");
      let dateInput = $(`#date${index}`).val();
      let notesInput = $(`#notes${index}`).val();
      let leadId = $('#leadId').val();
      const totalFollowUps = 5;
      console.log('index--->' + index, 'date-->' + dateInput, 'notesInput-->' + notesInput);
      if (!dateInput || !notesInput) {
        alert("Please fill all fields before submitting.");
        return;
      }
      let formData = {
        leadId: leadId,
        follow_up_index: index,
        follow_up_date: dateInput,
        follow_up_notes: notesInput,
        action: "leadsmanager.setLeadFolloUp",
      };
      $.ajax({
        url: "api.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function (response) {
          console.log('response==>', response);
          if (response && response.status === 'success') {
            Swal.fire({
              title: `Follow Up ${index} Submitted Successfully!`,
              icon: "success",
              draggable: true,
            });
            $(`#submitBtn${index}`).hide();
            renderFollowUpData();
            if (index < totalFollowUps) {
              $(`#followUp${index + 1}`).show();
            }
            $(`#date${index}`).prop("readonly", true);
            $(`#notes${index}`).prop("readonly", true);
            $(`#followUp${index}`).addClass("readonly");
          } else {
            Swal.fire({
              title: response.message,
              icon: "error",
              draggable: true,
            });
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error: ", error);
          alert("Failed to submit follow-up. Please try again.");
        }
      });
    });
    $(document).on("submit", "#leadAssignUserForm", function (e) {
      e.preventDefault();
      var form = $(this).serializeArray();
      var leadId = $('#leadId').val();
      form.push({
        name: "lead_id",
        value: leadId
      });
      console.log('form', form);
      $.ajax({
        type: "POST",
        url: "api.php",
        data: $.param(form),
        dataType: "JSON",
        success: function (response) {
          $("#leadAssignUserForm")[0].reset();
          Swal.fire({
            title: response.message,
            icon: "success",
            draggable: true,
          });
        },
        error: function (response) {
          Swal.fire({
            title: response.message,
            icon: "error",
            draggable: true,
          });
        },
      });
    });

    $(document).on("click", "#createNoteSvg", function () {
      $("#smallNoteModal").toggleClass("d-none");
    });
    $(document).on("click", ".editmodal", function () {
      let leadId = $(this).attr("data-lead-id");
      $.ajax({
        url: "api.php",
        type: "POST",
        dataType: "json",
        data: {
          action: "leadsmanager.getLeadsDetails",
          lead_id: leadId
        },
        success: function (response) {
          if (response.status === "success" && response.leadsDetail.length > 0) {
            let lead = response.leadsDetail[0];
            let modalTitle = `Edit Lead ${lead.PRIMARY_NAME} (#${lead.LEAD_STORE_ID}) 
                    <span class='text text-danger'> ${formatAMPM(lead.CREATED_AT)} </span>`;
            $("#modalTitle").html(modalTitle);
            let convertedLead = convertKeysToCamelCase(lead);
            let leadDetailsHtml = '';
            let colClass = 'col-sm-6 col-md-4';
            leadDetailsHtml += `<div class="card-body">
                      <div class="datagrid">`;

            for (let key in convertedLead) {
              if (convertedLead.hasOwnProperty(key) && convertedLead[key] !== null && convertedLead[
                  key] !== "") {
                let badgeHtml = '';
                if (key.toLowerCase() === 'source') {
                  badgeHtml = `<span class="badge bg-primary">${convertedLead[key]}</span>`;
                } else if (key.toLowerCase() === 'status') {
                  badgeHtml = `<span class="badge bg-secondary">${convertedLead[key]}</span>`;
                } else if (key.toLowerCase() === 'product') {
                  badgeHtml = `<span class="badge bg-success">${convertedLead[key]}</span>`;
                }

                let contentHtml = convertedLead[key];
                if (key.toLowerCase() === 'creator') {
                  contentHtml = `
                <div class="d-flex align-items-center">
                    <span class="avatar avatar-xs me-2 rounded" style="background-image: url(./static/avatars/000m.jpg)"></span>
                    ${convertedLead[key]}
                </div>`;
                } else if (key.toLowerCase() === 'edge network') {
                  contentHtml = `<span class="status status-green">${convertedLead[key]}</span>`;
                } else if (key.toLowerCase() === 'avatars list') {
                  contentHtml = `
                <div class="avatar-list avatar-list-stacked">
                    <span class="avatar avatar-xs rounded" style="background-image: url(./static/avatars/000m.jpg)"></span>
                    <span class="avatar avatar-xs rounded">JL</span>
                    <span class="avatar avatar-xs rounded" style="background-image: url(./static/avatars/002m.jpg)"></span>
                    <span class="avatar avatar-xs rounded" style="background-image: url(./static/avatars/003m.jpg)"></span>
                    <span class="avatar avatar-xs rounded" style="background-image: url(./static/avatars/000f.jpg)"></span>
                    <span class="avatar avatar-xs rounded">+3</span>
                </div>`;
                } else if (key.toLowerCase() === 'checkbox') {
                  contentHtml = `
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" checked="">
                    <span class="form-check-label">Click me</span>
                </label>`;
                } else if (key.toLowerCase() === 'icon') {
                  contentHtml = `
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-green icon-2">
                    <path d="M5 12l5 5l10 -10"></path>
                </svg>
                ${convertedLead[key]}`;
                } else if (key.toLowerCase() === 'form control') {
                  contentHtml =
                    `<input type="text" class="form-control form-control-flush" placeholder="Input placeholder">`;
                }
                leadDetailsHtml += `
            <div class="datagrid-item">
                <div class="datagrid-title">${formatKey(key)}:</div> 
                <div class="datagrid-content">${badgeHtml || contentHtml}</div> 
            </div>
        `;
              }
            }
            leadDetailsHtml += `</div></div>`;
            $("#leadDetailsList").html(leadDetailsHtml);

            $("#leadId").val(leadId);
            $("#editModal").modal("show");
          } else {
            alert("No lead details found.");
          }
        },
        error: function () {
          console.log("AJAX error.");
        }
      });
    });
  });

  function getLeadStatus(lead_id) {
    $.ajax({
      url: "api.php",
      type: "POST",
      data: {
        action: "leadsmanager.getLeadStatus",
        lead_id: lead_id,
      },
      dataType: "json",
      success: function (response) {
        console.log("response===>", response);
        if (response && response.status == 'success') {
          var leadStatuses = response.leadStatuses;
          var currentStatus = response.leadStatus.STATUS; // Actual selected status

          var html = '';
          $.each(leadStatuses, function (key, value) {
            var selected = key === currentStatus ? 'selected' : ''; // ✅ Compare key, not value
            html += '<option value="' + key + '" ' + selected + '>' + value +
            '</option>'; // ✅ Use key as value, value as display text
          });

          $('#lead_status').html(html).selectpicker('refresh');
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error: ", error);
        alert("Failed to fetch lead status. Please try again.");
      }
    });
  }

  function loadUsers(lead_id = '') {
    $.ajax({
      type: "POST",
      url: "api.php",
      data: {
        action: "leadsmanager.getLeadAssignedToUsers",
        lead_id: lead_id,
      },
      dataType: "json",
      success: function (data) {
        console.log('data==>', data);
        if (data.status === 'success') {
          var users = data.users;
          var html = '';
          $.each(users, function (index, user) {
            var selected = user.LEAD_ID !== null ? 'selected' : '';
            html += '<option value="' + user.ID + '" ' + selected + '>' + user.USER_NAME + '</option>';
          });
          $('#user_id').html(html).selectpicker('refresh');
        } else {
          console.error('Error fetching data:', data.message);
        }
      },
      error: function (xhr, status, error) {
        console.log('AJAX Error:', xhr.responseText);
      }
    });
  }

  function getLeadNotes(leadId) {
    $.ajax({
      url: "api.php",
      type: "POST",
      data: {
        action: "leadsmanager.getLeadNotes",
        lead_id: leadId
      },
      dataType: "json", // Ensure response is parsed as JSON
      success: function (response) {
        console.log("Response:", response);
        if (response.status == "success") {
          $(".note-list").html(response.html);
        } else {
          $(".note-list").html("<p class='text-muted'>No notes found.</p>");
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        alert("Failed to fetch notes. Please try again.");
      }
    });
  }

  function formatKey(key) {
    return key.toLowerCase().replace(/_/g, " ").replace(/\b\w/g, (char) => char.toUpperCase());
  }

  function convertKeysToCamelCase(obj) {
    let newObj = {};
    for (let key in obj) {
      if (obj.hasOwnProperty(key)) {
        let camelCaseKey = key.toLowerCase().replace(/_([a-z])/g, (match, letter) => letter.toUpperCase());
        newObj[camelCaseKey] = obj[key];
      }
    }
    return newObj;
  }

  function formatAMPM(date) {
    if (typeof date === 'string') {
      date = new Date(date);
    }
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
  }
</script>