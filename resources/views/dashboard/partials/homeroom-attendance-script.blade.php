@php
    $homeroomStudentsForJs = $homeroomStudents ?? [];
    $attendanceStatusesForJs = $attendanceStatuses ?? ['hadir', 'izin', 'sakit', 'alpha'];
@endphp

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dashboard = document.querySelector('[data-dashboard]');

        if (!dashboard) {
            return;
        }

        const portalPrefix = '/' + (dashboard.dataset.dashboardPortal || 'walikelas');
        const csrfToken = '{{ csrf_token() }}';
        const homeroomStudents = @json($homeroomStudentsForJs);
        const attendanceStatuses = @json($attendanceStatusesForJs);

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

        const fetchAttendanceRecords = async function (form, classId, date) {
            if (!classId || !date) {
                applyAttendanceRecords(form, []);

                return [];
            }

            const query = new URLSearchParams({
                school_class_id: classId,
                attendance_date: date,
            });

            try {
                const response = await fetch(portalPrefix + '/rekap-absensi-kelas?' + query.toString(), {
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
                roster.innerHTML = '<div class="portal-roster-empty">Pilih kelas terlebih dahulu.</div>';
                if (summary) {
                    summary.textContent = 'Pilih kelas untuk menampilkan siswa.';
                }

                return;
            }

            if (filteredStudents.length === 0) {
                roster.innerHTML = '<div class="portal-roster-empty">Belum ada siswa pada kelas ini.</div>';
                if (summary) {
                    summary.textContent = 'Belum ada siswa pada kelas ini.';
                }

                return;
            }

            if (summary) {
                summary.textContent = filteredStudents.length + ' siswa tampil pada kelas ini.';
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

        const checkAttendanceStatus = async function (form, date) {
            if (!date) {
                return true;
            }

            try {
                const response = await fetch(portalPrefix + '/status-absensi?date=' + encodeURIComponent(date), {
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

        dashboard.querySelectorAll('[data-class-attendance-form]').forEach(function (form) {
            const classSelect = form.querySelector('[data-class-select]');
            const dateInput = form.querySelector('[data-attendance-date]');
            let renderSequence = 0;

            const render = async function () {
                const sequence = ++renderSequence;
                const classId = classSelect?.value || '';
                const date = dateInput?.value || '';

                renderRoster(form, homeroomStudents, classId);
                clearFormFeedback(form);
                await checkAttendanceStatus(form, date);

                if (sequence !== renderSequence) {
                    return;
                }

                await fetchAttendanceRecords(form, classId, date);
            };

            classSelect?.addEventListener('change', render);
            dateInput?.addEventListener('change', render);
            render();

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                submitAttendance(form, portalPrefix + '/absensi-kelas', {
                    school_class_id: classSelect?.value || '',
                    attendance_date: dateInput?.value || '',
                });
            });
        });

        dashboard.addEventListener('click', function (event) {
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
