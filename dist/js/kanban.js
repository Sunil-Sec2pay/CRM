
    const statuses = ['NEW', 'CONTACTED', 'QUALIFIED', 'OPPORTUNITY', 'DEMO_SCHEDULED', 'DEMO_DONE','IN_NEGOTIATION','CONVERTED','DISQAULIFIED','LOST','LIVE','OTHER'];
    getLeads(statuses);
    
    function getLeads(statuses){
        $.ajax({
            url: 'api.php', 
            type: 'POST',
            data: {action:'leadsmanager.getLeads'},
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
    }
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
                onAdd: function (e) {
                    var parent = findParentWithClass(e.toComponent._$element[0],'list');
                    const newStatus = parent.children[0].innerText;
                    const lead_id = $(e.itemElement).data('lead-id'); 
                    const lead_status = $(e.itemElement).data('lead-status');
                    if (!lead_id || !newStatus) {
                        alert('Lead ID or new status not assigned.');
                        return;
                    }
                    $.ajax({
                        url: 'api.php',
                        type: 'POST',
                        data: {
                            action: 'leadsmanager.leadStatusUpdate',
                            lead_id: lead_id,
                            new_lead_status: newStatus,
                            lead_status: lead_status,
                        },
                        dataType:"JSON",
                        success: function (response) {
                            console.log('Server Response:status ', response);
                            if (response.status == 'success') {
                                console.log('Server Response:message ', response.message);
                                Swal.fire({
                                    title: response.message,
                                    icon: "success",
                                    draggable: true,
                                  });
                                  $container.dxScrollView("instance");
                            } else {
                                Swal.fire({
                                    title: response.message,
                                    icon: "error",
                                    draggable: true,
                                  });
                            }
                        },
                        error: function (error) {
                            console.log("error===>",error);
                            Swal.fire({
                                title:'somthing went to wrong',
                                icon: "error",
                                draggable: true
                              });
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
        setBackgroundColor();
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
        const statusColors = {
            "To Do": "#f1c40f",
            "In Progress": "#3498db",
            "Pending": "#e67e22",
            "Hold": "#9b59b6",
            "Done": "#2ecc71",
            "Completed": "#27ae60",
            "Lost": "#e74c3c"
        };
        const bgColor = statusColors[lead.status] || "#ecf0f1"; 
        const lightColor = hexToRgba(bgColor, 0.2); 
        const $item = $('<div>')
            .addClass('card dx-card p-3 mb-3 shadow-sm position-relative')
            .attr('data-lead-id', lead.lead_id).css({
                "background": `linear-gradient(145deg, ${lightColor}, ${bgColor})`,
                "box-shadow": `4px 4px 10px rgba(0,0,0,0.1), -4px -4px 10px rgba(255,255,255,0.4)`,
                "border-radius": "10px"
            }).appendTo($container);
    
        const $headerRow = $('<div>').addClass('d-flex justify-content-between align-items-center mb-2').appendTo($item);
        const $badges = $('<div>').appendTo($headerRow);
    
        if (lead.lead_source) {
            $('<span>').addClass('badge bg-primary me-2').attr('title', `Lead Source: ${lead.lead_source}`).text(lead.lead_source).appendTo($badges);
        }
        if (lead.lead_product) {
            $('<span>').addClass('badge bg-success').attr('title', `Lead Product: ${lead.lead_product}`).text(lead.lead_product).appendTo($badges);
        }
    
        const $dropdown = $('<div>').addClass('dropdown').appendTo($headerRow);
        $('<i>').addClass('fas fa-ellipsis-v text-secondary cursor-pointer').attr('data-bs-toggle', 'dropdown').attr('title', 'Actions')
            .appendTo($dropdown);
    
        $('<ul>').addClass('dropdown-menu dropdown-menu-end').html(`
                <li><a class="dropdown-item" href="#"><i class="fas fa-phone-alt me-2"></i>Call Now</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-sms me-2"></i>Sms Now</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-calendar-check me-2"></i>Meeting</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Edit</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-trash me-2"></i>Delete</a></li>
            `).appendTo($dropdown);
    
        const $nameRow = $('<div>').addClass('d-flex justify-content-between align-items-center mb-1').appendTo($item);
        $('<div>').addClass('fw-bold text-primary').attr('title', `Lead Name: ${lead.lead_name}`)
            .html(`${lead.lead_name} <span class="text-secondary">(#${lead.lead_phone})</span>`)
            .appendTo($nameRow).on('click', function (event) {
                event.stopPropagation();
                window.location.href = `add-lead.php?lead_id=${lead.lead_id}`;
            });
        // $('<div>')
        //     .addClass('small text-danger')
        //     .attr('title', `Created on: ${new Date(lead.created_timestamp).toLocaleString()}`)
        //     .text(new Date(lead.created_timestamp).toLocaleDateString())
        //     .appendTo($nameRow);
    
        // const $phoneRow = $('<div>').addClass('d-flex align-items-center mt-2').appendTo($item);
        // $('<i>').addClass('fa fa-phone me-2').attr('title', 'Phone').appendTo($phoneRow);
        // $('<div>')
        //     .addClass('small')
        //     .attr('title', `Phone Number: ${lead.lead_phone}`)
        //     .text(lead.lead_phone)
        //     .appendTo($phoneRow);
    
        const $userRow = $('<div>').addClass('d-flex justify-content-between align-items-center mt-2').appendTo($item);
        $('<div>').addClass('small').attr('title', `Assigned to: ${lead.assigned_users || lead.lead_owner}`)
            .append('<i class="fa fa-users" aria-hidden="true"></i>').append(` ${lead.assigned_users || lead.lead_owner}`)
            .appendTo($userRow);
    
        $('<i>').addClass('fa fa-user-plus').attr('title', 'Assign User')
            .on('click', function () {
                loadUsers(lead.assigned_user_ids);
                $("#leadAssignUserForm")[0].reset();
                $("#lead_id").val(lead.lead_id);
                $('#leadAssignUserModal').modal('show');
            }).appendTo($userRow);
    }
    
    function hexToRgba(hex, alpha) {
        let r = parseInt(hex.substring(1, 3), 16);
        let g = parseInt(hex.substring(3, 5), 16);
        let b = parseInt(hex.substring(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
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
    

    