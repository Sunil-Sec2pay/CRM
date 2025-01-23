$(() => {
    const statuses = ['To Do', 'In Progress', 'Pending', 'Hold', 'Done', 'Completed','Lost'];
       $.ajax({
        url: 'fetch_data.php', 
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            console.log("response===>",response);
            const tasks = response.tasks;
            const employees = response.employees;
            renderKanban($('#kanban'), statuses, tasks, employees);
        },
        error: function () {
            alert("Error fetching data from the server.");
        }
    });

    function renderKanban($container, statusList, tasks, employees) {
        statusList.forEach((status) => {
            renderList($container, status, tasks, employees);
        });

        $container.addClass('scrollable-board').dxScrollView({
            direction: 'horizontal',
            showScrollbar: 'always',
        });

        $container.addClass('sortable-lists').dxSortable({
            filter: '.list',
            itemOrientation: 'horizontal',
            handle: '.list-title',
            moveItemOnDrop: true,
        });
        $container.find('.sortable-cards').each(function () {
            $(this).dxSortable({
                group: 'tasksGroup',
                moveItemOnDrop: true,
                onDragChange: function (e) {
                    if (e.toElement) {
                        $(e.toElement).closest('.list').addClass('highlight-target'); 
                    }
                    if (e.fromElement) {
                        $(e.fromElement).closest('.list').removeClass('highlight-target'); 
                    }
                },
                onDragEnd: function (e) {
                    var parent = findParentWithClass(e.toComponent._$element[0],'list');
                    const newStatus = parent.children[0].innerText;
                    const lead_id = $(e.itemElement).data('lead-id'); 
                    const lead_status = $(e.itemElement).data('lead-status');
                    if (!lead_id || !newStatus) {
                        alert('Lead ID or new status not assigned.');
                        return;
                    }
                    $.ajax({
                        url: 'lead_status_update.php',
                        type: 'POST',
                        data: {
                            lead_id: lead_id,
                            new_lead_status: newStatus,
                            lead_status: lead_status,
                            action: "lead_status_update",
                        },
                        success: function (response) {
                            console.log('Server Response:', response);
                            if (response.success == 'success') {
                                alert(response.message);
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function () {
                            alert('Error communicating with the server.');
                        },
                    });
                },
            });
        });
    }
    function findParentWithClass(element, className) {
        while (element && !element.classList.contains(className)) {
          element = element.parentElement;
        }
        return element && element.classList.contains(className) ? element : null;
      }

    function renderList($container, status, tasks, employees) {
        const $list = $('<div>').addClass('list').appendTo($container);
        renderListTitle($list, status);
        const listTasks = tasks.filter((task) => task.lead_status === status);
        renderCards($list, listTasks, employees);
    }

    function renderListTitle($container, status) {
        $('<div>').addClass('list-title').text(status).appendTo($container);
    }

    function renderCards($container, tasks, employees) {
        const $scroll = $('<div>').appendTo($container);
        const $items = $('<div>').appendTo($scroll);

        tasks.forEach((task) => {
            renderCard($items, task, employees);
        });

        $scroll.addClass('scrollable-list').dxScrollView({
            direction: 'vertical',
            showScrollbar: 'always',
        });

        $items.addClass('sortable-cards').dxSortable({
            group: 'tasksGroup',
            moveItemOnDrop: true,
        });
       
    }
    function renderCard($container, lead) {
        const $item = $('<div>').addClass('card dx-card p-3 mb-3').attr('data-lead-id', lead.lead_id).attr('data-lead-status', lead.lead_status).appendTo($container);
        const $header = $('<div>').addClass('d-flex justify-content-between align-items-start mb-2').appendTo($item);
        const $leftSection = $('<div>').appendTo($header);
        $('<div>').addClass('card-subject fw-bold').html(`${lead.lead_name} <span class="text-secondary">(#lead-${new Date().getFullYear()}-${String(lead.lead_id).padStart(5, '0')})</span>`).appendTo($leftSection).on('click', function (event) {
                event.stopPropagation();
                window.location.href = `add-lead.php?lead_id=${lead.lead_id}`;
            });
        const $dropdown = $('<div>').addClass('dropdown').appendTo($header);
        $('<button>').addClass('btn btn-light btn-sm').attr('type', 'button').attr('data-bs-toggle', 'dropdown').html('<i class="fas fa-cog"></i>').appendTo($dropdown);
        const $dropdownMenu = $('<ul>').addClass('dropdown-menu').appendTo($dropdown);
        $('<li>').append('<a class="dropdown-item" href="#">Call Now</a>').appendTo($dropdownMenu);
        $('<li>').append('<a class="dropdown-item" href="#">Sms Now</a>').appendTo($dropdownMenu);
        $('<li>').append('<a class="dropdown-item" href="#">Meeting</a>').appendTo($dropdownMenu);
        $('<li>').append('<a class="dropdown-item" href="#">Edit</a>').appendTo($dropdownMenu);
        $('<li>').append('<a class="dropdown-item" href="#">Delete</a>').appendTo($dropdownMenu);
        if (lead.lead_remark) {
             $('<div>').addClass('small text-secondary mb-2').text(lead.lead_remark.length > 50 ? `${lead.lead_remark.substring(0, 50)}...` : lead.lead_remark).attr('title', lead.lead_remark).appendTo($item);
        }
      
        $('<div>').addClass('small text-danger').append($('<i>').addClass('fa fa-calendar-check me-1')).append(document.createTextNode(new Date(lead.create_timestamp).toLocaleString())).appendTo($leftSection);

        const $infoLine1 = $('<div>').addClass('d-flex align-items-center mt-2').appendTo($item);
        if (lead.lead_source) {
            $('<span>').addClass(`badge ${getStatusBadgeClass(lead.lead_source)} me-2`).text(lead.lead_source).appendTo($infoLine1);
        }
        if (lead.lead_product) {
            $('<span>').addClass('badge bg-success me-2').text(lead.lead_product).appendTo($infoLine1);
        }
        const $infoLine2 = $('<div>').addClass('d-flex align-items-center mt-2').appendTo($item);
        $('<div>').addClass('text-secondary me-2').html(`<i class="fas fa-phone-alt me-1"></i>${lead.lead_phone}`).appendTo($infoLine2);
        const $assignedUsers = $('<div>').appendTo($infoLine2);
        if (lead.assigned_users) {
            if (Array.isArray(lead.assigned_users)) {
                lead.assigned_users.forEach((user) => {
                    const initials = (user.split(' ')[0][0] || '') + (user.split(' ')[1]?.[0] || '');
                    $('<span>').addClass('badge bg-primary me-1').text(initials).attr('title', user)
                        .appendTo($assignedUsers);
                     });
            } else if (typeof lead.assigned_users === 'string') {
                const initials = lead.assigned_users.split(' ').map((word) => word[0]).join('');
                $('<span>').addClass('badge bg-primary me-1').text(initials).attr('title', lead.assigned_users).appendTo($assignedUsers);
            }
        } else {
            $('<span>').addClass('text-secondary me-2').text('Unassigned').appendTo($assignedUsers);
        }
        $('<i>').addClass('fa fa-user-plus text-secondary').attr('title', 'Assign User').on('click', function () {
            $("#leadAssignUserForm")[0].reset();
            $('#leadAssignUserModal').modal('show');

        }).appendTo($infoLine2);
        $('<hr>').addClass('my-2').appendTo($item);
        const $footer = $('<div>').addClass('d-flex justify-content-between align-items-center').appendTo($item);
        const lastActivity = lead.last_activity || new Date().toISOString();
        const $activity = $('<div>').addClass('small text-secondary').appendTo($footer);
        $('<i>').addClass('fas fa-clock me-1').appendTo($activity);
        $('<span>').text(`${new Date(lastActivity).toLocaleString()}`).appendTo($activity);
        const commentCount = lead.comment_count || 0;
        $('<div>').addClass('small text-secondary').html(`<i class="fas fa-comments"></i> ${commentCount}`).appendTo($footer);
    }
    
    function getStatusBadgeClass(status) {
        switch (status.toLowerCase()) {
            case 'facebook':
                return 'bg-primary';
            case 'instamart':
                return 'bg-secondary';
            case 'google':
                return 'bg-warning';
            case 'instagram':
                return 'bg-info';
            case 'website':
                return 'bg-dark';
            case 'website':
                return 'bg-danger';
            default:
                return 'bg-success';
        }
    }
    
});