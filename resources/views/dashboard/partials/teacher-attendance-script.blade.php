@php
    $teacherStudentsForJs = $teacherStudents ?? [];
    $attendanceStatusesForJs = $attendanceStatuses ?? ['hadir', 'izin', 'sakit', 'alpha'];
    $scheduleRowsForJs = $scheduleRows ?? [];
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dashboard = document.querySelector('[data-dashboard]');

        if (!dashboard) {
            return;
        }

        const csrfToken = '{{ csrf_token() }}';
        const teacherStudents = @json($teacherStudentsForJs);
        const attendanceStatuses = @json($attendanceStatusesForJs);
        let scheduleRows = @json($scheduleRowsForJs);

        const escapeHtml = function (value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const statusOptions = function () {
            return attendanceStatuses.map(function (status) {
                const label = status.charAt(0).toUpperCase() + status.slice(1);

                return '<option value="' + escapeHtml(status) + '">' + escapeHtml(label) + '</option>';
            }).join('');
        };

        const scheduleOptionLabel = function (schedule) {
            return [schedule.time, schedule.subject, schedule.class_name].filter(Boolean).join(' | ');
        };

        const updateScheduleMeta = function (schedules) {
            const countLabel = document.querySelector('[data-schedule-count-label]');
            const studentCount = document.querySelector('[data-schedule-student-count]');
            const totalStudents = (schedules || []).reduce(function (total, schedule) {
                return total + Number(schedule.students_count || 0);
            }, 0);

            if (countLabel) {
                countLabel.textContent = (schedules || []).length + ' jadwal pada tanggal dipilih';
            }

            if (studentCount) {
                studentCount.textContent = totalStudents + ' siswa pada jadwal';
            }
        };

        const renderScheduleOptions = function (select, schedules, preferredId) {
            if (!select) {
                return;
            }

            if (!schedules || schedules.length === 0) {
                select.innerHTML = '<option value="">Belum ada jadwal</option>';
                return;
            }

            select.innerHTML = schedules.map(function (schedule) {
                return '<option value="' + escapeHtml(schedule.id) + '" data-class-id="' + escapeHtml(schedule.school_class_id) + '">' +
                    escapeHtml(scheduleOptionLabel(schedule)) +
                '</option>';
            }).join('');

            const selectedExists = schedules.some(function (schedule) {
                return String(schedule.id) === String(preferredId || '');
            });

            select.value = selectedExists ? String(preferredId) : String(schedules[0].id);
        };

        const renderScheduleCards = function (form, schedules, selectedId) {
            const cards = form.querySelector('[data-assignment-cards]');

            if (!cards) {
                return;
            }

            if (!schedules || schedules.length === 0) {
                cards.innerHTML = '<div class="portal-assignment-empty">Belum ada jadwal mengajar pada tanggal ini.</div>';
                return;
            }

            cards.innerHTML = schedules.map(function (schedule) {
                const isActive = String(schedule.id) === String(selectedId || '');
                const status = schedule.status || {};

                return '' +
                    '<button class="portal-assignment-card' + (isActive ? ' is-active' : '') + '" type="button" data-assignment-card="' + escapeHtml(schedule.id) + '">' +
                        '<span class="portal-assignment-card__time">' + escapeHtml(schedule.time) + '</span>' +
                        '<span class="portal-assignment-card__main">' +
                            '<strong>' + escapeHtml(schedule.subject) + '</strong>' +
                            '<small>' + escapeHtml(schedule.class_name) + ' | ' + escapeHtml(schedule.students_count || 0) + ' siswa</small>' +
                        '</span>' +
                        '<span class="portal-assignment-card__meta">' +
                            '<small>' + escapeHtml(schedule.day_name || '') + '</small>' +
                            '<b>' + escapeHtml(schedule.room || '-') + '</b>' +
                        '</span>' +
                        '<span class="portal-assignment-card__status is-' + escapeHtml(status.tone || 'neutral') + '">' + escapeHtml(status.label || 'Terjadwal') + '</span>' +
                    '</button>';
            }).join('');
        };

        const fetchSchedulesForDate = async function (date) {
            if (!date) {
                return [];
            }

            const query = new URLSearchParams({date});

            try {
                const response = await fetch('/guru-mapel/absensi-siswa/jadwal?' + query.toString(), {
                    headers: {'Accept': 'application/json'},
                });
                const payload = await response.json();

                if (!response.ok) {
                    return [];
                }

                return Array.isArray(payload?.data?.schedules) ? payload.data.schedules : [];
            } catch (error) {
                return [];
            }
        };

        const setFormFeedback = function (form, message, isError) {
            const feedback = form.querySelector('[data-attendance-feedback]');

            if (!feedback) {
                return;
            }

            feedback.textContent = message;
            feedback.classList.toggle('is-error', Boolean(isError));
            feedback.classList.remove('d-none');
        };

        const clearFormFeedback = function (form) {
            const feedback = form.querySelector('[data-attendance-feedback]');

            if (!feedback) {
                return;
            }

            feedback.textContent = '';
            feedback.classList.remove('is-error');
            feedback.classList.add('d-none');
        };

        const applyAttendanceRecords = function (form, records) {
            const recordsByStudent = new Map();

            (records || []).forEach(function (record) {
                if (record?.student_id) {
                    recordsByStudent.set(String(record.student_id), record);
                }
            });

            form.querySelectorAll('[data-student-id]').forEach(function (row) {
                const record = recordsByStudent.get(String(row.dataset.studentId));
                const statusField = row.querySelector('[data-student-status]');
                const notesField = row.querySelector('[data-student-notes]');

                if (statusField) {
                    statusField.value = record?.status || 'hadir';
                }

                if (notesField) {
                    notesField.value = record?.notes || '';
                }
            });
        };

        const fetchAttendanceRecords = async function (form, assignmentId, date) {
            if (!assignmentId || !date) {
                applyAttendanceRecords(form, []);

                return [];
            }

            const query = new URLSearchParams({
                teaching_assignment_id: assignmentId,
                attendance_date: date,
            });

            try {
                const response = await fetch('/guru-mapel/rekap-absensi-mapel?' + query.toString(), {
                    headers: {'Accept': 'application/json'},
                });
                const payload = await response.json();

                if (!response.ok) {
                    return [];
                }

                const records = Array.isArray(payload?.data) ? payload.data : [];

                applyAttendanceRecords(form, records);

                return records;
            } catch (error) {
                return [];
            }
        };

        const renderRoster = function (form, students, classId) {
            const roster = form.querySelector('[data-attendance-students]');
            const summary = form.querySelector('[data-roster-summary]');
            const filteredStudents = students.filter(function (student) {
                return String(student.school_class_id || '') === String(classId || '');
            });

            if (!roster) {
                return;
            }

            if (!classId) {
                roster.innerHTML = '<div class="portal-roster-empty">Pilih jadwal terlebih dahulu.</div>';
                if (summary) {
                    summary.textContent = 'Pilih jadwal untuk menampilkan siswa.';
                }

                return;
            }

            if (filteredStudents.length === 0) {
                roster.innerHTML = '<div class="portal-roster-empty">Belum ada siswa pada jadwal ini.</div>';
                if (summary) {
                    summary.textContent = 'Belum ada siswa pada jadwal ini.';
                }

                return;
            }

            if (summary) {
                summary.textContent = filteredStudents.length + ' siswa tampil pada jadwal ini.';
            }

            let tableHTML = '<div class="table-responsive"><table class="table table-bordered table-hover align-middle mb-0">' +
                '<thead>' +
                    '<tr>' +
                        '<th style="width: 50px;" class="text-center">No</th>' +
                        '<th>Nama</th>' +
                        '<th>NISN</th>' +
                        '<th style="width: 200px;">Kehadiran</th>' +
                        '<th style="width: 300px;">Keterangan</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>';

            tableHTML += filteredStudents.map(function (student, index) {
                return '' +
                '<tr data-student-id="' + escapeHtml(student.id) + '" data-search-item>' +
                    '<td class="text-center">' + (index + 1) + '</td>' +
                    '<td><strong>' + escapeHtml(student.name) + '</strong></td>' +
                    '<td>' + escapeHtml(student.nisn || student.nik || '-') + '</td>' +
                    '<td>' +
                        '<select class="form-select" data-student-status aria-label="Status ' + escapeHtml(student.name) + '">' + statusOptions() + '</select>' +
                    '</td>' +
                    '<td>' +
                        '<input class="form-control" type="text" data-student-notes placeholder="Catatan">' +
                    '</td>' +
                '</tr>';
            }).join('');

            tableHTML += '</tbody></table></div>';
            roster.innerHTML = tableHTML;
        };

        const checkAttendanceStatus = async function (form, endpoint, date) {
            if (!date) {
                return true;
            }

            try {
                const response = await fetch(endpoint + '?date=' + encodeURIComponent(date), {
                    headers: {'Accept': 'application/json'},
                });
                const payload = await response.json();
                const allowed = Boolean(payload?.data?.allowed);
                const message = payload?.data?.message || '';
                const submitButton = form.querySelector('[type="submit"]');

                form.querySelectorAll('[data-student-id] select, [data-student-id] input, [data-mark-status]').forEach(function (field) {
                    field.disabled = !allowed;
                });

                if (submitButton) {
                    submitButton.disabled = !allowed;
                }

                if (!allowed) {
                    setFormFeedback(form, message || 'Absensi tidak dapat diisi pada tanggal ini.', true);
                }

                return allowed;
            } catch (error) {
                return true;
            }
        };

        const submitAttendance = async function (form, endpoint, baseFields) {
            const rows = Array.from(form.querySelectorAll('[data-student-id]'));

            if (rows.length === 0) {
                setFormFeedback(form, 'Belum ada siswa yang bisa disimpan.', true);

                return;
            }

            const formData = new FormData();

            Object.entries(baseFields).forEach(function ([key, value]) {
                formData.append(key, value);
            });

            rows.forEach(function (row, index) {
                formData.append('attendances[' + index + '][student_id]', row.dataset.studentId);
                formData.append('attendances[' + index + '][status]', row.querySelector('[data-student-status]')?.value || 'hadir');
                formData.append('attendances[' + index + '][notes]', row.querySelector('[data-student-notes]')?.value || '');
            });

            const submitButton = form.querySelector('[type="submit"]');

            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const payload = await response.json();

                if (!response.ok) {
                    const messages = payload?.errors
                        ? Object.values(payload.errors).flat().join(' ')
                        : payload?.message || 'Absensi gagal disimpan.';

                    setFormFeedback(form, messages, true);

                    return;
                }

                setFormFeedback(form, payload?.message || 'Absensi berhasil disimpan.', false);
                applyAttendanceRecords(form, Array.isArray(payload?.data) ? payload.data : []);
            } catch (error) {
                setFormFeedback(form, 'Koneksi bermasalah saat menyimpan absensi.', true);
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        };

        dashboard.querySelectorAll('[data-subject-attendance-form]').forEach(function (form) {
            const assignmentSelect = form.querySelector('[data-assignment-select]');
            const dateInput = form.querySelector('[data-attendance-date]');
            let renderSequence = 0;

            const render = async function (options = {}) {
                const sequence = ++renderSequence;
                const shouldRefreshSchedules = Boolean(options.refreshSchedules);
                const date = dateInput?.value || '';
                const previousAssignmentId = assignmentSelect?.value || '';

                if (shouldRefreshSchedules) {
                    scheduleRows = await fetchSchedulesForDate(date);

                    if (sequence !== renderSequence) {
                        return;
                    }

                    renderScheduleOptions(assignmentSelect, scheduleRows, previousAssignmentId);
                    updateScheduleMeta(scheduleRows);
                }

                const selectedOption = assignmentSelect?.selectedOptions[0];
                const assignmentId = assignmentSelect?.value || '';

                renderRoster(form, teacherStudents, selectedOption?.dataset.classId || '');
                renderScheduleCards(form, scheduleRows, assignmentId);
                clearFormFeedback(form);

                await checkAttendanceStatus(form, '/guru-mapel/status-absensi', date);

                if (sequence !== renderSequence) {
                    return;
                }

                await fetchAttendanceRecords(form, assignmentId, date);
            };

            renderScheduleOptions(assignmentSelect, scheduleRows, assignmentSelect?.value || '');
            updateScheduleMeta(scheduleRows);

            assignmentSelect?.addEventListener('change', function () {
                render({refreshSchedules: false});
            });
            dateInput?.addEventListener('change', function () {
                render({refreshSchedules: true});
            });
            render();

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                submitAttendance(form, '/guru-mapel/absensi-mapel', {
                    teaching_assignment_id: assignmentSelect?.value || '',
                    attendance_date: dateInput?.value || '',
                });
            });
        });

        dashboard.addEventListener('click', function (event) {
            const scheduleCard = event.target.closest('[data-assignment-card]');

            if (scheduleCard) {
                const form = scheduleCard.closest('form');
                const select = form?.querySelector('[data-assignment-select]');

                if (select) {
                    select.value = scheduleCard.dataset.assignmentCard || '';
                    select.dispatchEvent(new Event('change'));
                }

                return;
            }

            const button = event.target.closest('[data-mark-status]');

            if (!button) {
                return;
            }

            const form = button.closest('form');
            const status = button.dataset.markStatus || 'hadir';

            form?.querySelectorAll('[data-student-status]').forEach(function (select) {
                select.value = status;
            });
        });
    });
</script>
