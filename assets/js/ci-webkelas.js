(function ($) {
  'use strict';

  const apiUrl = document.body.dataset.apiUrl.replace(/\/$/, '');
  const storageTheme = 'webkelas.theme';
  const state = { route: 'overview', search: '', taskCourse: 'all', taskStatus: 'open', scheduleGroup: 'all', courses: [], tasks: [], announcements: [], worklog: [], user: null, profileLinkUrl: '', profileLinkExpiresAt: '', activeComposer: null, mentionRange: null, announcementDraft: null };
  const titles = { overview: 'Ringkasan kelas', announcements: 'Info kelas', tasks: 'Checklist saya', schedule: 'Jadwal kuliah', courses: 'Mata kuliah', worklog: 'Worklog', profile: 'Profil saya' };
  const esc = (value) => $('<div>').text(value == null ? '' : value).html();
  const safeHttpUrl = (value) => { try { const url = new URL(String(value || '')); return ['http:', 'https:'].includes(url.protocol) ? url.href : ''; } catch (_) { return ''; } };
  const linkifyText = (value) => esc(value).replace(/(https?:\/\/[^\s<]+)/gi, (url) => `<a href="${url.replace(/[),.;!?]+$/g, '')}" target="_blank" rel="noopener noreferrer">${url}</a>`);
  const norm = (value) => String(value || '').toLowerCase();
  const courseById = (id) => state.courses.find((course) => course.id === id);
  const announcementById = (id) => state.announcements.find((item) => String(item.id) === String(id));
  const iconFor = (id) => ({ 'teknik-kompilasi': 'bi-cpu', spk: 'bi-diagram-3', 'pemrograman-2': 'bi-code-slash', 'basis-data-2': 'bi-database', iot: 'bi-router', 'kerja-praktek': 'bi-briefcase', 'mobile-programming': 'bi-phone', rpl: 'bi-kanban' }[id] || 'bi-book');
  const nameInitials = (name) => String(name || 'WK').split(/\s+/).slice(0, 2).map((part) => part.charAt(0)).join('').toUpperCase();

  function api(path, options = {}) {
    return $.ajax($.extend({ url: `${apiUrl}/${path}`, dataType: 'json' }, options)).then((response) => response.data !== undefined ? response.data : response);
  }

  function refreshData() {
    return Promise.all([api('session'), api('courses'), api('class-tasks'), api('announcements'), api('worklog')]).then(([user, courses, tasks, announcements, worklog]) => {
      state.user = user; state.courses = courses; state.tasks = tasks; state.announcements = announcements; state.worklog = worklog; render();
    }).catch((xhr) => {
      if (xhr && xhr.status === 401) { location.href = document.body.dataset.baseUrl + location.hash; return; }
      $('#app').html('<section class="panel error-state"><i class="bi bi-database-x"></i><h2>Data kelas belum siap</h2><p>Periksa koneksi database dan daftar mahasiswa. Jika baru pertama menjalankan, buka <code>/init</code> untuk membuat tabel mata kuliah.</p></section>');
    });
  }

  function scheduleRows(group, limit) {
    return state.courses.filter((course) => group === 'all' || String(course.group) === String(group)).slice(0, limit || 99).map((course) => `<div class="timeline-row"><div class="timeline-time">${esc(course.time.split(' - ')[0])}<br><span>${esc(course.time.split(' - ')[1])}</span></div><div class="timeline-track"><span class="timeline-dot"></span></div><div class="timeline-info"><strong>${esc(course.name)} <span class="mini-badge ${course.group === 2 ? 'teal' : ''}">Kel. ${course.group}</span></strong><span>${esc(course.lecturer)} · ${esc(course.room)}</span></div></div>`).join('');
  }

  function courseTasks(courseId) { return state.tasks.filter((task) => String(task.course_id || '') === String(courseId || '')); }
  function progress(courseId) { const tasks = courseTasks(courseId); return { total: tasks.length, done: tasks.filter((task) => Number(task.checked) === 1).length, open: tasks.filter((task) => Number(task.checked) !== 1).length }; }
  function taskShareLink(task) { return `${location.origin}${location.pathname}#announcement/${task.announcement_id}`; }
  function announcementLink(item) { return `${location.origin}${location.pathname}#announcement/${item.id}`; }

  function taskCard(task) {
    const checked = Number(task.checked) === 1;
    const reference = task.reference_url ? `<a class="task-reference" href="${esc(task.reference_url)}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Buka link referensi</a>` : '';
    const participation = Number(task.student_total) > 0 ? `<span class="task-participation"><i class="bi bi-people"></i> ${Number(task.checked_count)} dari ${Number(task.student_total)} teman sudah cek</span>` : '';
    return `<article class="check-task-card ${checked ? 'is-checked' : ''}"><button class="check-control" data-action="toggle-check" data-id="${task.id}" aria-label="${checked ? 'Batalkan checklist' : 'Tandai selesai'}"><i class="bi ${checked ? 'bi-check2-circle' : 'bi-circle'}"></i></button><div class="check-task-content"><div class="check-task-kicker">${esc(task.course_short || task.course_name || 'Info global')} ${task.due_label ? `<span>· ${esc(task.due_label)}</span>` : ''}</div><strong>${esc(task.title)}</strong><div class="check-task-meta">${task.announcement_id ? `<a href="#announcement/${task.announcement_id}" data-route="announcement/${task.announcement_id}"><i class="bi bi-megaphone"></i> ${esc(task.announcement_title || 'Detail info')}</a>` : ''}${participation}</div>${reference}</div><span class="check-state ${checked ? 'done' : ''}">${checked ? 'Selesai' : 'Belum'}</span></article>`;
  }

  function announcementCard(item) {
    const tasks = state.tasks.filter((task) => String(task.announcement_id) === String(item.id));
    const done = tasks.filter((task) => Number(task.checked) === 1).length;
    const courseLabel = item.course_short || item.course_name || 'Info global';
    const deadline = item.deadline_at ? new Date(item.deadline_at.replace(' ', 'T')).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '';
    return `<article class="info-card ${Number(item.is_read) === 0 ? 'is-unread' : ''}"><div class="info-card-top"><span class="info-course-tag ${item.course_id ? '' : 'global'}"><i class="bi ${item.course_id ? 'bi-book' : 'bi-broadcast'}"></i>${esc(courseLabel)}</span>${Number(item.is_read) === 0 ? '<span class="unread-pill">Baru</span>' : ''}</div><a class="info-card-title" href="#announcement/${item.id}" data-route="announcement/${item.id}">${esc(item.title)}</a><p class="info-card-body">${linkifyText(item.body)}</p><div class="info-card-meta"><span><i class="bi bi-person"></i>${esc(item.author || 'Ketua kelas')}</span><span><i class="bi bi-clock"></i>${new Date(item.created_at.replace(' ', 'T')).toLocaleDateString('id-ID')}</span>${deadline ? `<span><i class="bi bi-alarm"></i>${esc(deadline)}</span>` : ''}</div><div class="info-card-footer"><span><i class="bi bi-check2-square"></i>${done}/${tasks.length} checklist selesai olehmu</span><div><button class="text-link" data-action="copy-announcement" data-id="${item.id}"><i class="bi bi-clipboard"></i> Salin isi</button><button class="text-link" data-action="copy-link" data-id="${item.id}"><i class="bi bi-link-45deg"></i> Salin link</button><a class="text-link" href="#announcement/${item.id}" data-route="announcement/${item.id}">Buka detail <i class="bi bi-arrow-up-right"></i></a></div></div></article>`;
  }

  function statCards() {
    const open = state.tasks.filter((task) => Number(task.checked) !== 1).length;
    const done = state.tasks.length - open;
    const unread = state.announcements.filter((item) => Number(item.is_read) === 0).length;
    return `<div class="stats-grid"><div class="stat-card"><div><small>Checklist tersisa</small><strong>${open}</strong><em>khusus akunmu</em></div><span class="stat-icon orange"><i class="bi bi-list-check"></i></span></div><div class="stat-card"><div><small>Selesai</small><strong>${done}</strong><em>dari ${state.tasks.length} item kelas</em></div><span class="stat-icon teal"><i class="bi bi-check2-circle"></i></span></div><div class="stat-card"><div><small>Info belum dibaca</small><strong>${unread}</strong><em>broadcast tersimpan</em></div><span class="stat-icon red"><i class="bi bi-bell"></i></span></div><div class="stat-card"><div><small>Mata kuliah</small><strong>${state.courses.length}</strong><em>kelas 06TPLE004</em></div><span class="stat-icon purple"><i class="bi bi-book"></i></span></div></div>`;
  }

  function courseProgressCard(course) {
    const p = progress(course.id);
    const percent = p.total ? Math.round(p.done / p.total * 100) : 0;
    return `<article class="course-progress-card" data-course="${esc(course.id)}"><div class="course-progress-head"><span class="course-color ${esc(course.color)}"><i class="bi ${iconFor(course.id)}"></i></span><span class="mini-badge">${p.open} tersisa</span></div><strong>${esc(course.name)}</strong><div class="progress-track"><span style="width:${percent}%"></span></div><small>${p.done} dari ${p.total} checklist selesai · buka detail</small></article>`;
  }

  function overview() {
    const displayName = state.user ? state.user.display_name : 'Mahasiswa';
    const unread = state.announcements.filter((item) => Number(item.is_read) === 0).length;
    const open = state.tasks.filter((task) => Number(task.checked) !== 1).slice(0, 4);
    const latest = state.announcements.slice(0, 3);
    return `<section class="welcome-card"><div class="welcome-copy"><span class="eyebrow"><i class="bi bi-stars me-2"></i>${esc(displayName)} · KELAS 06TPLE004</span><h1>Info kelas tidak lagi tenggelam.</h1><p>Broadcast ketua kelas, referensi materi, dan checklist tugasmu tersimpan rapi per mata kuliah.</p><div class="welcome-actions"><button class="btn btn-primary" data-view="tasks"><i class="bi bi-check2-square"></i> Lanjut checklist</button><button class="btn btn-outline-light" data-view="announcements"><i class="bi bi-megaphone"></i> ${unread} info baru</button></div></div><div class="hero-orbit" aria-hidden="true"><div class="orbit"></div><div class="planet"><i class="bi bi-mortarboard-fill"></i></div><i class="bi bi-stars star star-one"></i><i class="bi bi-plus-lg star star-two"></i></div></section>${statCards()}<div class="dashboard-grid"><section class="panel"><div class="panel-heading"><div><h2>Yang perlu kamu lanjutkan</h2><p>Progres ini hanya mengubah checklist akunmu.</p></div><button class="text-link" data-view="tasks">Semua checklist</button></div><div class="focus-list">${open.length ? open.map((task) => `<div class="focus-item"><span class="focus-icon"><i class="bi bi-circle"></i></span><div><strong>${esc(task.title)}</strong><p>${esc(task.course_short || task.course_name || 'Info global')} · ${esc(task.due_label || 'Tanpa tenggat')}</p></div><button class="quick-check" data-action="toggle-check" data-id="${task.id}" aria-label="Tandai selesai"><i class="bi bi-check2"></i></button></div>`).join('') : '<div class="empty-inline"><i class="bi bi-check-circle-fill"></i> Semua checklist selesai. Kerja bagus!</div>'}</div></section><section class="panel"><div class="panel-heading"><div><h2>Jadwal hari Sabtu</h2><p>Semester ganjil 2026/2027</p></div><button class="text-link" data-view="schedule">Lihat jadwal</button></div><div class="timeline">${scheduleRows('all', 4)}</div></section><section class="panel panel-wide"><div class="panel-heading"><div><h2>Progress per mata kuliah</h2><p>Sisa tugas dihitung untuk akunmu.</p></div><button class="text-link" data-view="courses">Semua mata kuliah</button></div><div class="course-progress-grid">${state.courses.map(courseProgressCard).join('')}</div></section><section class="panel panel-wide"><div class="panel-heading"><div><h2>Info kelas terbaru</h2><p>Riwayat broadcast agar instruksi mudah ditemukan lagi.</p></div><button class="text-link" data-view="announcements">Semua info</button></div><div class="announcement-list">${latest.length ? latest.map(announcementCard).join('') : '<div class="empty-inline">Belum ada info kelas.</div>'}</div></section></div>`;
  }

  function filteredTasks() {
    const query = norm(state.search);
    return state.tasks.filter((task) => {
      const haystack = norm(`${task.title} ${task.announcement_title} ${task.course_name} ${task.course_short}`);
      const byStatus = state.taskStatus === 'all' || (state.taskStatus === 'open' ? Number(task.checked) !== 1 : Number(task.checked) === 1);
      const byCourse = state.taskCourse === 'all' || (state.taskCourse === 'global' ? !task.course_id : task.course_id === state.taskCourse);
      return (!query || haystack.includes(query)) && byCourse && byStatus;
    });
  }

  function tasks() {
    const list = filteredTasks();
    const done = list.filter((task) => Number(task.checked) === 1).length;
    return `<div class="view-heading"><div><span class="eyebrow">Checklist milikmu</span><h1>Checklist saya</h1><p>Setiap tanda selesai hanya mengubah progres NIM yang sedang login.</p></div><span class="status-chip ${list.length === done ? 'done' : 'todo'}">${list.length - done} belum selesai</span></div><div class="toolbar"><label class="search-inline"><i class="bi bi-search"></i><input class="form-control" id="taskSearch" value="${esc(state.search)}" placeholder="Cari tugas atau mata kuliah"></label><select class="form-select" id="taskCourseFilter"><option value="all">Semua mata kuliah</option>${state.courses.map((course) => `<option value="${esc(course.id)}" ${state.taskCourse === course.id ? 'selected' : ''}>${esc(course.short_name || course.name)}</option>`).join('')}<option value="global" ${state.taskCourse === 'global' ? 'selected' : ''}>Info global</option></select><select class="form-select" id="taskStatusFilter"><option value="open" ${state.taskStatus === 'open' ? 'selected' : ''}>Belum selesai</option><option value="done" ${state.taskStatus === 'done' ? 'selected' : ''}>Sudah selesai</option><option value="all" ${state.taskStatus === 'all' ? 'selected' : ''}>Semua status</option></select></div><div class="checklist-list">${list.length ? list.map(taskCard).join('') : '<div class="task-empty"><i class="bi bi-check2-circle"></i><strong>Tidak ada item checklist</strong><p>Ubah filter atau buka Info kelas untuk melihat broadcast ketua.</p></div>'}</div>`;
  }

  function announcements() {
    const query = norm(state.search);
    const list = state.announcements.filter((item) => norm(`${item.title} ${item.body} ${item.course_name} ${item.course_short}`).includes(query));
    const action = state.user && state.user.role === 'leader' ? '<button class="btn btn-primary" data-action="open-announcement"><i class="bi bi-plus-lg"></i> Buat info & checklist</button>' : '';
    return `<div class="view-heading"><div><span class="eyebrow">Broadcast kelas tersimpan</span><h1>Info kelas</h1><p>Pesan tidak hilang tertimpa chat. Buka detail atau salin link checklist untuk dibagikan.</p></div>${action}</div><div class="announcement-feed">${list.length ? list.map(announcementCard).join('') : '<div class="task-empty"><i class="bi bi-megaphone"></i><strong>Info tidak ditemukan</strong><p>Belum ada broadcast dengan kata kunci tersebut.</p></div>'}</div>`;
  }

  function announcementDetail(id) {
    const item = announcementById(id);
    if (!item) return '<section class="panel error-state"><i class="bi bi-file-earmark-x"></i><h2>Info tidak ditemukan</h2><p>Info mungkin sudah dihapus atau tautannya salah.</p><button class="btn btn-primary mt-3" data-view="announcements">Kembali ke Info kelas</button></section>';
    const list = state.tasks.filter((task) => String(task.announcement_id) === String(id));
    const done = list.filter((task) => Number(task.checked) === 1).length;
    const courseLabel = item.course_short || item.course_name || 'Info global';
    const sourceUrl = safeHttpUrl(item.source_url);
    const referenceUrl = safeHttpUrl(item.reference_url);
    const source = sourceUrl ? `<a class="source-link" href="${esc(sourceUrl)}" target="_blank" rel="noopener noreferrer"><i class="bi bi-shield-check"></i><span>Buka sumber instruksi</span><i class="bi bi-box-arrow-up-right ms-auto"></i></a>` : '';
    const link = referenceUrl ? `<a class="source-link" href="${esc(referenceUrl)}" target="_blank" rel="noopener noreferrer"><i class="bi bi-link-45deg"></i><span>Buka referensi atau link pengumpulan</span><i class="bi bi-box-arrow-up-right ms-auto"></i></a>` : '';
    const count = list.length ? `<span class="status-chip ${done === list.length ? 'done' : 'todo'}">${done} dari ${list.length} selesai</span>` : '';
    return `<button class="detail-back mb-3" data-view="announcements"><i class="bi bi-arrow-left me-1"></i> Semua info kelas</button><article class="announcement-detail"><div class="info-card-top"><span class="info-course-tag ${item.course_id ? '' : 'global'}"><i class="bi ${item.course_id ? 'bi-book' : 'bi-broadcast'}"></i>${esc(courseLabel)}</span><button class="text-link" data-action="copy-link" data-id="${item.id}"><i class="bi bi-link-45deg"></i> Salin link detail</button></div><h1>${esc(item.title)}</h1><div class="announcement-author"><i class="bi bi-person-circle"></i> ${esc(item.author || 'Ketua kelas')} · ${new Date(item.created_at.replace(' ', 'T')).toLocaleString('id-ID')}</div>${item.deadline_at ? `<div class="deadline-callout"><i class="bi bi-alarm"></i><span>Tenggat<strong>${new Date(item.deadline_at.replace(' ', 'T')).toLocaleString('id-ID')}</strong></span></div>` : ''}<div class="announcement-detail-body">${esc(item.body)}</div>${source}${link}<section class="detail-checklist"><div class="panel-heading"><div><h2>Checklist tugas</h2><p>Checklist terpisah untuk setiap mahasiswa.</p></div>${count}</div>${list.length ? `<div class="checklist-list">${list.map(taskCard).join('')}</div>` : '<div class="empty-inline">Belum ada checklist pada info ini.</div>'}</section></article>`;
  }

  function schedule() {
    const rows = state.courses.filter((course) => state.scheduleGroup === 'all' || String(course.group) === String(state.scheduleGroup));
    return `<div class="view-heading"><div><span class="eyebrow">Semester ganjil 2026/2027</span><h1>Jadwal kuliah</h1><p>Semua perkuliahan berlangsung hari Sabtu di Viktor, V.708.</p></div><button class="btn btn-secondary" data-action="print-schedule"><i class="bi bi-printer"></i> Cetak jadwal</button></div><section class="schedule-card"><div class="schedule-toolbar"><div><h2>Jadwal kelas 06TPLE004</h2><p>31 Agustus 2026 — 31 Januari 2027</p></div><div class="group-pills"><button class="group-pill ${state.scheduleGroup === 'all' ? 'active' : ''}" data-group="all">Semua</button><button class="group-pill ${state.scheduleGroup === '1' ? 'active' : ''}" data-group="1">Kelompok 1</button><button class="group-pill ${state.scheduleGroup === '2' ? 'active' : ''}" data-group="2">Kelompok 2</button></div></div><div class="schedule-table-wrap"><table class="schedule-table"><thead><tr><th>Kode</th><th>Mata kuliah</th><th>Dosen</th><th>Jam</th><th>Kel.</th><th>UTS</th><th>UAS</th><th>Ruang</th></tr></thead><tbody>${rows.map((course) => `<tr><td><strong>${esc(course.code)}</strong><small>${course.sks} SKS</small></td><td><strong>${esc(course.name)}</strong><small>${esc(course.day)}</small></td><td>${esc(course.lecturer)}</td><td class="time-cell">${esc(course.time)}</td><td><span class="mini-badge ${course.group === 2 ? 'teal' : ''}">${course.group}</span></td><td><span class="mode-tag ${norm(course.uts) === 'online' ? 'online' : ''}">${esc(course.uts)}</span></td><td><span class="mode-tag ${norm(course.uas) === 'online' ? 'online' : ''}">${esc(course.uas)}</span></td><td>${esc(course.room)}</td></tr>`).join('')}</tbody></table></div></section>`;
  }

  function courseMini(course) {
    const p = progress(course.id);
    return `<article class="course-mini" data-course="${esc(course.id)}"><div class="course-color ${esc(course.color)}"><i class="bi ${iconFor(course.id)}"></i></div><strong>${esc(course.name)}</strong><span class="course-code">${esc(course.code)}</span><span>${esc(course.time)}</span><span class="course-mini-progress">${p.open} checklist tersisa</span></article>`;
  }

  function courseCard(course) {
    const p = progress(course.id);
    return `<article class="course-card" data-course="${esc(course.id)}"><div class="course-card-head"><div><div class="course-color ${esc(course.color)}"><i class="bi ${iconFor(course.id)}"></i></div><h3>${esc(course.name)}</h3><span class="course-lecturer">${esc(course.lecturer)}</span></div><span class="mini-badge ${p.open ? '' : 'teal'}">${p.open} tersisa</span></div><div class="course-detail"><span class="detail-pill">${esc(course.code)}</span><span class="detail-pill">${course.sks} SKS</span><span class="detail-pill">${esc(course.time)}</span></div><div class="course-card-footer"><span>${esc(course.room)}</span><button type="button" data-action="filter-course" data-course-id="${esc(course.id)}">Checklist <i class="bi bi-arrow-up-right"></i></button></div></article>`;
  }

  function courses() {
    const list = state.courses.filter((course) => !state.search || norm(`${course.name} ${course.code} ${course.lecturer}`).includes(norm(state.search)));
    return `<div class="view-heading"><div><span class="eyebrow">Katalog kelas</span><h1>Mata kuliah</h1><p>Jadwal, materi, info kelas, dan sisa checklist tiap mata kuliah.</p></div></div><div class="course-view-grid">${list.length ? list.map(courseCard).join('') : '<div class="task-empty">Mata kuliah tidak ditemukan.</div>'}</div>`;
  }

  function profile() {
    const user = state.user || {};
    const linkMarkup = state.profileLinkUrl
      ? `<div class="profile-link-result"><label for="profileAccessLink">Tautan rahasia · berlaku sampai ${esc(state.profileLinkExpiresAt)}</label><div class="input-group"><input class="form-control" id="profileAccessLink" readonly value="${esc(state.profileLinkUrl)}"><button class="btn btn-outline-primary" data-action="copy-profile-link" type="button"><i class="bi bi-copy"></i> Salin</button></div></div>`
      : '<p class="profile-link-empty">Belum ada tautan aktif. Tautan hanya ditampilkan setelah dibuat dan tidak disimpan di browser ini.</p>';
    return `<div class="view-heading"><div><span class="eyebrow">Akun WebKelas</span><h1>Profil saya</h1><p>Kelola nama tampilan, password, dan tautan masuk cepat milikmu.</p></div></div><div class="profile-page-grid"><section class="panel"><div class="panel-heading"><div><h2>Data profil</h2><p>NIM dan peran berasal dari data mahasiswa kelas.</p></div></div><div class="profile-identity"><span class="avatar avatar-md">${esc(nameInitials(user.display_name))}</span><div><strong>${esc(user.display_name)}</strong><small>${esc(user.role === 'leader' ? 'Ketua kelas' : 'Mahasiswa')} · ${esc(user.nim)}</small></div></div><form id="profilePageForm"><div class="form-field"><label for="profilePageName">Nama tampilan</label><input class="form-control" id="profilePageName" name="display_name" maxlength="120" required value="${esc(user.display_name)}"></div><hr><p class="profile-password-note">Biarkan password baru kosong jika tidak ingin menggantinya.</p><div class="form-field"><label for="profilePageCurrentPassword">Password saat ini</label><input class="form-control" id="profilePageCurrentPassword" name="current_password" type="password" autocomplete="current-password"></div><div class="form-field"><label for="profilePageNewPassword">Password baru <span>(minimal 8 karakter)</span></label><input class="form-control" id="profilePageNewPassword" name="new_password" type="password" minlength="8" autocomplete="new-password"></div><button class="btn btn-primary" type="submit"><i class="bi bi-floppy"></i> Simpan profil</button></form></section><section class="panel"><div class="panel-heading"><div><h2>Tautan masuk cepat</h2><p>Masuk tanpa mengetik NIM dan password, lalu langsung membuka halaman profil.</p></div><span class="stat-icon purple"><i class="bi bi-shield-lock"></i></span></div><div class="profile-link-warning"><i class="bi bi-exclamation-triangle"></i><span>Tautan ini seperti password sementara: berlaku 15 menit, hanya sekali pakai, dan siapa pun yang memegangnya bisa masuk ke akunmu. Jangan bagikan di grup.</span></div><div class="d-flex flex-wrap gap-2 my-3"><button class="btn btn-primary" data-action="generate-profile-link" type="button"><i class="bi bi-link-45deg"></i> Buat tautan baru</button><button class="btn btn-light" data-action="revoke-profile-link" type="button"><i class="bi bi-x-circle"></i> Cabut tautan</button></div>${linkMarkup}<small class="d-block text-secondary mt-3">Tautan lama langsung tidak berlaku saat tautan baru dibuat atau dicabut.</small></section></div>`;
  }

  function courseDetail(course) {
    const list = courseTasks(course.id);
    const p = progress(course.id);
    const links = course.links.length ? course.links.map((link) => `<a class="source-link" href="${esc(link.url)}" target="_blank" rel="noopener"><i class="bi bi-link-45deg"></i><span>${esc(link.label)}</span><i class="bi bi-box-arrow-up-right ms-auto"></i></a>`).join('') : '<p class="text-secondary small mb-0">Belum ada link materi atau grup yang dicatat.</p>';
    const groupedAnnouncementIds = new Set(state.tasks.filter((task) => task.course_id === course.id && task.announcement_id).map((task) => String(task.announcement_id)));
    const info = state.announcements.filter((item) => item.course_id === course.id || groupedAnnouncementIds.has(String(item.id)));
    return `<button class="detail-back mb-3" data-view="courses"><i class="bi bi-arrow-left me-1"></i> Kembali ke mata kuliah</button><div class="course-detail-layout"><div><section class="course-detail-hero"><span class="eyebrow">${esc(course.code)} · ${course.sks} SKS</span><h1>${esc(course.name)}</h1><p>${esc(course.day)}, ${esc(course.time)} · Kelompok ${course.group} · ${esc(course.room)}</p><div class="lecturer-line"><span class="avatar avatar-sm">${esc(nameInitials(course.lecturer))}</span><div><strong>${esc(course.lecturer)}</strong><small>${course.whatsapp ? `WA ${esc(course.whatsapp)}` : 'Kontak belum dicatat'}</small></div></div></section><section class="course-detail-panel mt-3"><div class="detail-block"><h2>Progress checklist</h2><p class="course-progress-copy">${p.done} selesai · ${p.open} tersisa dari ${p.total} tugas</p><button class="btn btn-primary btn-sm" data-action="filter-course" data-course-id="${esc(course.id)}">Buka checklist matkul</button></div><div class="detail-block"><h2>Mode ujian</h2><div class="d-flex gap-2"><span class="mode-tag ${norm(course.uts) === 'online' ? 'online' : ''}">UTS · ${esc(course.uts)}</span><span class="mode-tag ${norm(course.uas) === 'online' ? 'online' : ''}">UAS · ${esc(course.uas)}</span></div></div><div class="detail-block"><h2>Link penting</h2><div class="d-grid gap-2">${links}</div></div></section></div><div><section class="course-detail-panel"><div class="panel-heading"><div><h2>Info mata kuliah</h2><p>Broadcast dari ketua kelas</p></div></div>${info.length ? `<div class="course-info-list">${info.map((item) => `<a href="#announcement/${item.id}" data-route="announcement/${item.id}"><strong>${esc(item.title)}</strong><small>${new Date(item.created_at.replace(' ', 'T')).toLocaleDateString('id-ID')} · ${item.task_total} item checklist</small></a>`).join('')}</div>` : '<p class="text-secondary small">Belum ada broadcast matkul ini.</p>'}</section><section class="course-detail-panel"><div class="panel-heading"><div><h2>Checklist tugas</h2><p>${p.open} item perlu dikerjakan</p></div></div>${list.length ? `<div class="checklist-list">${list.slice(0, 6).map(taskCard).join('')}</div>` : '<p class="text-secondary small">Belum ada checklist.</p>'}</section>${course.notes.length ? `<section class="course-detail-panel"><div class="panel-heading"><div><h2>Catatan mata kuliah</h2></div></div><ul class="detail-list">${course.notes.map((note) => `<li><i class="bi bi-check-circle-fill"></i><span>${esc(note)}</span></li>`).join('')}</ul></section>` : ''}</div></div>`;
  }

  function worklog() {
    return `<div class="view-heading"><div><span class="eyebrow">Riwayat perubahan proyek</span><h1>Worklog</h1><p>Catatan pengembangan WebKelas.</p></div></div><section class="panel"><div class="worklog-list">${state.worklog.map((entry) => `<article class="worklog-item"><time class="worklog-time">${esc(entry.logged_at)}</time><div class="worklog-node"><span></span></div><div class="worklog-body"><span class="worklog-type">${esc(entry.type)}</span><h3>${esc(entry.title)}</h3><p>${esc(entry.detail)}</p><code class="worklog-command">${esc(entry.command)}</code></div></article>`).join('') || '<div class="empty-inline">Belum ada catatan worklog.</div>'}</div></section>`;
  }

  function render() {
    const parts = state.route.split('/');
    const course = parts[0] === 'course' ? courseById(parts[1]) : null;
    let html;
    if (parts[0] === 'announcement') html = announcementDetail(parts[1]);
    else if (parts[0] === 'checklist' && parts[1] === 'course') { state.taskCourse = parts[2] || 'all'; html = tasks(); }
    else if (course) html = courseDetail(course);
    else if (state.route === 'tasks') html = tasks();
    else if (state.route === 'announcements') html = announcements();
    else if (state.route === 'schedule') html = schedule();
    else if (state.route === 'courses') html = courses();
    else if (state.route === 'worklog') html = worklog();
    else if (state.route === 'profile') html = profile();
    else html = overview();
    $('#app').html(html);
    $('.announcement-detail-body').each(function () { $(this).html(linkifyText($(this).text()).replace(/\n/g, '<br>')); });
    $('.check-task-content > strong').each(function () { $(this).html(linkifyText($(this).text())); });
    $('a[data-route^="announcement/"]').attr({ target: '_blank', rel: 'noopener noreferrer' });
    const navView = parts[0] === 'announcement' ? 'announcements' : (parts[0] === 'checklist' ? 'tasks' : course ? 'courses' : state.route);
    $('#pageTitle').text(titles[navView] || 'Detail mata kuliah');
    $('.nav-item-link').removeClass('active'); $(`.nav-item-link[data-view="${navView}"]`).addClass('active');
    $('.mobile-nav-item').removeClass('active'); $(`.mobile-nav-item[data-view="${navView}"]`).addClass('active');
    $('#taskCount').text(state.tasks.filter((task) => Number(task.checked) !== 1).length);
    $('#unreadCount').text(state.announcements.filter((item) => Number(item.is_read) === 0).length);
    $('#globalSearch').val(state.search);
    if (state.user) {
      const initials = nameInitials(state.user.display_name);
      $('#sidebarName').text(state.user.display_name); $('#sidebarRole').text(`${state.user.role === 'leader' ? 'Ketua kelas' : 'Mahasiswa'} · ${state.user.nim}`);
      $('#sidebarAvatar,#profileAvatar,#profileOpenTop').text(initials); $('#profileNim').text(state.user.nim); $('#profileRole').text(state.user.role === 'leader' ? 'Ketua kelas · 06TPLE004' : 'Mahasiswa · 06TPLE004'); $('#profileName').val(state.user.display_name);
    }
    document.title = `${titles[navView] || 'Detail mata kuliah'} · WebKelas`;
    if (parts[0] === 'announcement' && parts[1]) {
      const item = announcementById(parts[1]);
      if (item && Number(item.is_read) === 0) api(`announcements/${item.id}/read`, { method: 'POST' }).then(refreshData);
    }
  }

  function navigate(route) {
    state.route = route || 'overview';
    if (location.hash.slice(1) !== state.route) location.hash = state.route;
    if (state.route.indexOf('checklist/course/') !== 0) state.taskCourse = 'all';
    render(); $('#sidebar').removeClass('open'); $('#sidebarOverlay').removeClass('show'); window.scrollTo({ top: 0, behavior: 'smooth' });
  }
  function toast(message, icon = 'bi-check-circle') { $('#toastContainer').html(`<div class="toast show app-toast"><div class="toast-body"><i class="bi ${icon} me-2"></i>${esc(message)}</div></div>`); setTimeout(() => $('#toastContainer .toast').remove(), 2800); }
  function modal(id) { return bootstrap.Modal.getOrCreateInstance(document.querySelector(id)); }
  function openAnnouncementModal() {
    if (!state.user || state.user.role !== 'leader') return;
    $('#announcementForm')[0].reset(); state.announcementDraft = null; state.activeComposer = null; state.mentionRange = null;
    $('#courseMentionMenu,#extraCourseMentionMenu').prop('hidden', true).empty();
    $('#announcementComposePane').prop('hidden', false); $('#announcementPreviewPane').prop('hidden', true);
    $('#announcementPreviewButton').prop('hidden', false); $('#announcementEditButton,#announcementPublishButton').prop('hidden', true);
    const weeklyTitle = suggestedWeeklyTitle();
    $('#announcementTitle').val('').attr('placeholder', weeklyTitle);
    $('#announcementTitle').attr('data-weekly-suggestion', weeklyTitle);
    $('#announcementTitle').siblings('datalist').html(`<option value="${esc(weeklyTitle)}"></option>`);
    $('#weeklyTitleSuggestionButton').text(`Pakai saran: ${weeklyTitle}`).show();
    modal('#announcementModal').show();
  }

  function renderAnnouncementPreview() {
    const payload = state.announcementDraft || prepareAnnouncementPayload($('#announcementForm')[0]);
    const tasks = payload.tasks || [];
    const rows = tasks.map((task, index) => {
      const course = courseById(task.course_id);
      return `<article class="check-task-card preview-task-card"><span class="preview-task-number">${index + 1}</span><div class="check-task-content"><div class="check-task-kicker">${esc(course ? (course.short_name || course.name) : 'Checklist global')}${task.due_label ? ` <span>· ${esc(task.due_label)}</span>` : ''}</div><strong>${esc(task.title)}</strong>${task.reference_url ? `<a class="task-reference" href="${esc(task.reference_url)}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right"></i> Buka link referensi</a>` : ''}</div></article>`;
    }).join('');
    const info = payload.body ? `<section class="preview-info"><h3>Info untuk kelas</h3><div>${linkifyText(payload.body)}</div></section>` : '';
    const sourceUrl = safeHttpUrl(payload.source_url);
    const source = sourceUrl ? `<a class="source-link" href="${esc(sourceUrl)}" target="_blank" rel="noopener noreferrer"><i class="bi bi-shield-check"></i><span>Periksa sumber instruksi</span><i class="bi bi-box-arrow-up-right ms-auto"></i></a>` : '<p class="preview-empty">Sumber instruksi belum ditambahkan.</p>';
    $('#announcementPreviewPane').html(`<span class="eyebrow">Periksa sebelum dibagikan</span><h3 class="announcement-preview-title">${esc(payload.title)}</h3>${info}<section class="preview-info"><h3>Sumber instruksi</h3>${source}</section><div class="preview-heading"><strong>Checklist sesuai urutan pesan</strong><span>${tasks.length} item</span></div>${rows ? `<div class="checklist-list preview-checklist-list">${rows}</div>` : '<p class="preview-empty">Tidak ada checklist; pesan ini akan terbit sebagai info kelas.</p>'}<div class="preview-share-links"><i class="bi bi-link-45deg"></i> Link detail checklist ditambahkan saat info diterbitkan.</div>`);
  }

  function announcementShareText(item) {
    const tasks = state.tasks.filter((task) => String(task.announcement_id) === String(item.id));
    const whatsappBold = (value) => String(value || '').replace(/\(([^)\n]+)\)/g, '**($1)**');
    const lines = [`**${String(item.title || '').replace(/\r?\n/g, ' ')}**`, '', whatsappBold(String(item.body || '').trim())];
    if (item.deadline_at) lines.push('', `Tenggat: ${new Date(item.deadline_at.replace(' ', 'T')).toLocaleString('id-ID')}`);
    if (item.source_url) lines.push('', `Sumber instruksi: ${item.source_url}`);
    if (tasks.length) {
      lines.push('', 'CHECKLIST:');
      tasks.forEach((task) => {
        const course = task.course_short || task.course_name || 'Info global';
        lines.push(`- ${course}: ${whatsappBold(task.title)}${task.due_label ? ` — ${whatsappBold(task.due_label)}` : ''}`);
        if (task.reference_url) lines.push(`  Referensi: ${task.reference_url}`);
      });
    }
    lines.push('', `Link checklist & detail: ${announcementLink(item)}`, `Web kelas: ${location.origin}${location.pathname}`);
    return lines.filter((line, index, all) => line || (index > 0 && all[index - 1])).join('\n');
  }

  function suggestedWeeklyTitle() {
    const occupied = new Set();
    state.announcements.forEach((item) => {
      const match = String(item.title || '').match(/rekapan?\s+e[- ]learning\s+minggu(?:\s+ke)?\s*[-:]?\s*(\d+)/i);
      if (match) occupied.add(Number(match[1]));
    });
    let week = 1;
    while (occupied.has(week) && week < 53) week += 1;
    return `REKAPAN E-LEARNING MINGGU KE-${week}`;
  }

  function normalizeCourseLabel(value) { return String(value || '').toLowerCase().replace(/pemrograman\s*ii/g, 'pemrograman 2').replace(/basis\s+data\s*ii/g, 'basis data 2').replace(/\bii\b/g, '2').replace(/[^a-z0-9]+/g, ''); }
  function courseTagInLine(line) {
    const at = line.indexOf('@');
    if (at < 0) return { course_id: '', line };
    const after = line.slice(at + 1);
    const aliases = { 'basis-data-2': ['basis data 2'], 'pemrograman-2': ['pemrograman 2'], iot: ['teknologi internet of things', 'iot'] };
    const options = state.courses.flatMap((course) => [course.name, course.short_name, ...(aliases[course.id] || [])].filter(Boolean).map((label) => ({ course, label }))).sort((a, b) => b.label.length - a.label.length);
    const match = options.find((option) => after.toLowerCase().startsWith(option.label.toLowerCase()) && (!after[option.label.length] || /[\s(,.;:!?-]/.test(after[option.label.length])));
    if (!match) return { course_id: '', line, unresolved: after.split(/[\s(,.;:!?-]/)[0] };
    return { course_id: match.course.id, line: `${line.slice(0, at)}${after.slice(match.label.length)}`.trim() };
  }

  function taskRowsFromText(text, fallbackCourseId, dueFallback, onlyBullets) {
    const tasks = [];
    const remaining = [];
    const invalidMentions = [];
    const lines = String(text || '').split(/\r?\n/);
    lines.forEach((sourceLine) => {
      let line = sourceLine.trim();
      if (!line) { if (!onlyBullets) remaining.push(''); return; }
      const bullet = line.match(/^(?:[-*#\u2022]|\d+[.)])\s+(.+)$/);
      if (onlyBullets && !bullet) { remaining.push(sourceLine); return; }
      if (bullet) line = bullet[1].trim();
      if (/^menu\s+ceklist\*?$/i.test(line)) return;
      if (/^note\s*:/i.test(line)) { remaining.push(line); return; }
      if (!bullet && onlyBullets) { remaining.push(sourceLine); return; }
      const taggedCourse = courseTagInLine(line);
      if (taggedCourse.unresolved) invalidMentions.push(taggedCourse.unresolved);
      line = taggedCourse.line;
      const urls = (line.match(/https?:\/\/[^\s]+/gi) || []).map((url) => url.replace(/[),.;!?]+$/g, ''));
      if (urls.length) {
        line = line.replace(urls[0], '').trim();
        if (urls.length > 1) line += ` ${urls.slice(1).join(' ')}`;
      }
      let dueLabel = dueFallback || '';
      const due = line.match(/(?:DATELINE|DEADLINE)\s*:\s*(.+)$/i);
      if (due) { dueLabel = due[1].trim(); line = line.replace(/\s*[-–—]?\s*(?:DATELINE|DEADLINE)\s*:\s*.+$/i, '').trim(); }
      const meeting = line.match(/\(([^)]*pertemuan[^)]*)\)/i);
      const meetings = meeting ? (meeting[1].match(/\d+/g) || []) : [];
      const titles = meetings.length > 1 ? meetings.map((number) => line.replace(meeting[0], `(Pertemuan ${number})`)) : [line];
      const courseId = taggedCourse.course_id;
      titles.forEach((title) => {
        if (title) tasks.push({ title, course_id: courseId, due_label: dueLabel, reference_url: urls[0] || '' });
      });
    });
    return { tasks, body: remaining.join('\n').replace(/\n{3,}/g, '\n\n').trim(), invalidMentions };
  }

  function prepareAnnouncementPayload(form) {
    const values = Object.fromEntries(new FormData(form).entries());
    const bodyResult = taskRowsFromText(values.body, '', values.due_label, true);
    const extraResult = taskRowsFromText(values.tasks, '', values.due_label, true);
    const invalid = bodyResult.invalidMentions.concat(extraResult.invalidMentions);
    if (invalid.length) throw new Error(`Pilih mata kuliah dari saran untuk @${invalid[0]}.`);
    const manualTasks = extraResult.tasks;
    const taskMap = new Map();
    bodyResult.tasks.concat(manualTasks).forEach((task) => {
      const key = `${task.course_id}|${task.title.toLowerCase()}|${task.due_label}`;
      if (!taskMap.has(key)) taskMap.set(key, task);
    });
    let detail = [bodyResult.body, extraResult.body].filter(Boolean).join('\n\n');
    const originalLines = String(values.body || '').split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
    const titleKey = String(values.title || '').toLowerCase().replace(/[^a-z0-9]+/g, '');
    if (detail && originalLines.length && (originalLines[0].toLowerCase().replace(/[^a-z0-9]+/g, '').startsWith(titleKey) || /^rekapan?elearningminggu/.test(originalLines[0].toLowerCase().replace(/[^a-z0-9]+/g, '')))) {
      detail = detail.split(/\r?\n/).filter((line, index) => index !== 0 || (!line.toLowerCase().replace(/[^a-z0-9]+/g, '').startsWith(titleKey) && !/^rekapan?elearningminggu/.test(line.toLowerCase().replace(/[^a-z0-9]+/g, '')))).join('\n').trim();
    }
    const reference = String(values.reference_url || '').trim();
    const source = String(values.source_url || '').trim();
    if (reference && !safeHttpUrl(reference)) throw new Error('Link referensi harus berupa URL http atau https.');
    if (source && !safeHttpUrl(source)) throw new Error('Link sumber harus berupa URL http atau https.');
    const inlineUrl = (detail.match(/https?:\/\/[^\s]+/i) || [])[0] || '';
    values.body = detail || (taskMap.size ? `Checklist forum diskusi untuk ${values.title}. Setiap item dapat ditandai selesai secara pribadi.` : '');
    values.course_id = '';
    values.reference_url = reference || inlineUrl.replace(/[),.;!?]+$/g, '');
    values.source_url = source;
    values.tasks = Array.from(taskMap.values());
    return values;
  }
  function textareaCaretPosition(textarea, position) {
    const style = window.getComputedStyle(textarea); const mirror = document.createElement('div');
    ['fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'letterSpacing', 'textTransform', 'textAlign', 'lineHeight', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth', 'boxSizing', 'whiteSpace', 'wordWrap'].forEach((key) => { mirror.style[key] = style[key]; });
    const rect = textarea.getBoundingClientRect(); mirror.style.position = 'fixed'; mirror.style.left = `${rect.left}px`; mirror.style.top = `${rect.top}px`; mirror.style.width = `${rect.width}px`; mirror.style.height = 'auto'; mirror.style.visibility = 'hidden'; mirror.style.overflow = 'hidden';
    mirror.textContent = textarea.value.slice(0, position); const marker = document.createElement('span'); marker.textContent = textarea.value.slice(position, position + 1) || '\u200b'; mirror.appendChild(marker); document.body.appendChild(mirror);
    const markerRect = marker.getBoundingClientRect(); const wrapperRect = textarea.parentElement.getBoundingClientRect(); const coords = { left: Math.max(0, markerRect.left - wrapperRect.left), top: markerRect.bottom - wrapperRect.top + 3 }; mirror.remove(); return coords;
  }

  function showCourseMentions(textarea) {
    if (!textarea) return;
    const cursor = textarea.selectionStart; const before = textarea.value.slice(0, cursor); const lineStart = before.lastIndexOf('\n') + 1; const lineBefore = before.slice(lineStart);
    const match = lineBefore.match(/(?:^|[\s(])@([\w .-]*)$/);
    const $menu = $(textarea).siblings('.mention-menu');
    if (!match) { $menu.prop('hidden', true); state.mentionRange = null; return; }
    const query = match[1].trim(); const atPosition = cursor - match[1].length - 1;
    state.activeComposer = textarea; state.mentionRange = { start: atPosition, end: cursor };
    const term = normalizeCourseLabel(query);
    const matches = state.courses.filter((course) => !term || normalizeCourseLabel(`${course.name} ${course.short_name} ${course.code}`).includes(term)).slice(0, 7);
    $menu.html(matches.map((course) => `<button type="button" class="mention-option" data-course-id="${esc(course.id)}"><span class="course-color ${esc(course.color)}"><i class="bi ${iconFor(course.id)}"></i></span><span><strong>${esc(course.short_name || course.name)}</strong><small>${esc(course.name)} · ${esc(course.code)}</small></span></button>`).join(''));
    if (!matches.length) { $menu.prop('hidden', true); return; }
    const coords = textareaCaretPosition(textarea, cursor); $menu.css({ left: `${coords.left}px`, top: `${coords.top}px` }).prop('hidden', false);
  }

  function selectMention(courseId) {
    const textarea = state.activeComposer; const range = state.mentionRange; const course = courseById(courseId);
    if (!textarea || !range || !course) return;
    const insert = `@${course.name} `; const value = textarea.value;
    textarea.value = `${value.slice(0, range.start)}${insert}${value.slice(range.end)}`;
    const cursor = range.start + insert.length; textarea.focus(); textarea.setSelectionRange(cursor, cursor);
    $(textarea).siblings('.mention-menu').prop('hidden', true); state.mentionRange = null; $(textarea).trigger('input'); $(textarea).siblings('.mention-menu').prop('hidden', true);
  }

  $(function () {
    if (!$('#weeklyTitleSuggestions').length) $('#announcementTitle').after('<datalist id="weeklyTitleSuggestions"></datalist>');
    $('#announcementTitle').attr('list', 'weeklyTitleSuggestions');
    if (!$('#weeklyTitleSuggestionButton').length) $('#announcementTitle').after('<button type="button" id="weeklyTitleSuggestionButton" class="weekly-title-suggestion" data-action="use-weekly-title"></button>');
    $('#announcementTitle').closest('.form-field').find('label').html('Judul info <span>(saran minggu yang belum ada di arsip; bebas diedit)</span>');
    $('#announcementTitle').attr('maxlength', 180);
    $('#announcementTasks').closest('.form-field').find('label').html('Pesan tambahan <span>(opsional, ikut menjadi bagian dari info kelas)</span>');
    $('#announcementTasks').attr('placeholder', 'Tambahkan catatan lain atau checklist dengan awalan - / * / #');
    $('#announcementBody,#announcementTasks').removeAttr('required');
    $('#announcementDeadline').closest('.row').hide();
    $('#announcementReference').closest('.form-field').hide();
    if (localStorage.getItem(storageTheme) === 'dark') { $('body').addClass('dark-mode'); $('#themeToggle i').attr('class', 'bi bi-sun'); }
    state.route = location.hash.slice(1) || 'overview';
    refreshData();
    $(window).on('hashchange', () => { state.route = location.hash.slice(1) || 'overview'; render(); });
    $(document).on('click', '[data-view]', function (event) { event.preventDefault(); navigate($(this).data('view')); });
    $(document).on('click', '[data-route]', function (event) { if (this.target === '_blank') return; event.preventDefault(); navigate($(this).data('route')); });
    $(document).on('click', '[data-course]', function () { navigate(`course/${$(this).data('course')}`); });
    $(document).on('click', '[data-action="open-announcement"]', openAnnouncementModal);
    $(document).on('click', '[data-action="use-weekly-title"]', function () { $('#announcementTitle').val($('#announcementTitle').attr('data-weekly-suggestion') || '').trigger('input').trigger('change'); });
    $(document).on('click', '[data-action="filter-course"]', function (event) { event.preventDefault(); event.stopPropagation(); navigate(`checklist/course/${$(this).data('course-id')}`); });
    $(document).on('click', '[data-action="toggle-check"]', function () {
      const button = $(this); const task = state.tasks.find((item) => String(item.id) === String(button.data('id'))); if (!task) return;
      button.prop('disabled', true);
      api(`class-tasks/${task.id}/check`, { method: 'POST', contentType: 'application/json', data: JSON.stringify({ checked: Number(task.checked) !== 1 }) }).then((result) => { task.checked = result.checked ? 1 : 0; render(); }).catch(() => toast('Checklist tidak berhasil disimpan.', 'bi-exclamation-circle'));
    });
    $(document).on('click', '[data-action="copy-link"]', async function () {
      const item = announcementById($(this).data('id')); if (!item) return;
      try { await navigator.clipboard.writeText(announcementLink(item)); toast('Link detail berhasil disalin.', 'bi-link-45deg'); }
      catch (error) { window.prompt('Salin link info kelas ini:', announcementLink(item)); }
    });
    $(document).on('click', '[data-action="copy-announcement"]', async function () {
      const item = announcementById($(this).data('id')); if (!item) return;
      const text = announcementShareText(item);
      try { await navigator.clipboard.writeText(text); toast('Judul, pesan, checklist, dan link web berhasil disalin.', 'bi-clipboard-check'); }
      catch (error) { window.prompt('Salin seluruh info kelas untuk dibagikan:', text); }
    });
    $(document).on('click', '.group-pill', function () { state.scheduleGroup = String($(this).data('group')); render(); });
    $(document).on('click', '[data-action="print-schedule"]', () => window.print());
    $(document).on('input', '#taskSearch', function () { state.search = $(this).val(); render(); });
    $(document).on('change', '#taskCourseFilter', function () { state.taskCourse = $(this).val(); if (state.route.startsWith('checklist/course/')) { state.route = 'tasks'; location.hash = 'tasks'; } render(); });
    $(document).on('change', '#taskStatusFilter', function () { state.taskStatus = $(this).val(); render(); });
    $('#globalSearch').on('input', function () { state.search = $(this).val(); if (state.route.startsWith('announcement/')) navigate('announcements'); else if (state.route !== 'tasks' && state.route !== 'announcements' && state.route !== 'courses') navigate('announcements'); else render(); });
    $('#announcementBody,#announcementTasks').on('input click keyup', function () { showCourseMentions(this); });
    $('#announcementBody,#announcementTasks').on('keydown', function (event) {
      const $menu = $(this).siblings('.mention-menu');
      if (event.key === 'Escape') { $menu.prop('hidden', true); return; }
      if ((event.key === 'Tab' || event.key === 'Enter') && !$menu.prop('hidden')) { const $first = $menu.find('.mention-option').first(); if ($first.length) { event.preventDefault(); selectMention($first.data('course-id')); } }
    });
    $(document).on('click', '.mention-option', function () { selectMention($(this).data('course-id')); });
    $(document).on('click', function (event) { if (!$(event.target).closest('.announcement-textarea-wrap').length) $('#courseMentionMenu,#extraCourseMentionMenu').prop('hidden', true); });
    $('#announcementPreviewButton').on('click', function () {
      let payload;
      try { payload = prepareAnnouncementPayload($('#announcementForm')[0]); }
      catch (error) { toast(error.message, 'bi-exclamation-circle'); return; }
      if (!payload.title.trim() || (!payload.body.trim() && !payload.tasks.length)) { toast('Isi judul dan pesan kelas atau setidaknya satu checklist.', 'bi-exclamation-circle'); return; }
      state.announcementDraft = payload;
      try { renderAnnouncementPreview(); } catch (error) { toast(error.message, 'bi-exclamation-circle'); return; }
      $('#announcementComposePane').prop('hidden', true); $('#announcementPreviewPane').prop('hidden', false);
      $('#announcementPreviewButton').prop('hidden', true); $('#announcementEditButton,#announcementPublishButton').prop('hidden', false);
    });
    $('#announcementEditButton').on('click', function () { $('#announcementComposePane').prop('hidden', false); $('#announcementPreviewPane').prop('hidden', true); $('#announcementPreviewButton').prop('hidden', false); $('#announcementEditButton,#announcementPublishButton').prop('hidden', true); state.announcementDraft = null; });
    $('#announcementForm').on('submit', function (event) {
      event.preventDefault();
      let payload = state.announcementDraft;
      if (!payload) { $('#announcementPreviewButton').trigger('click'); return; }
      api('announcements', { method: 'POST', contentType: 'application/json', data: JSON.stringify(payload) }).then((result) => {
        modal('#announcementModal').hide(); state.announcementDraft = null; toast('Info kelas diterbitkan. Link detail bisa disalin dari kartu broadcast.'); state.search = ''; return refreshData().then(() => navigate(`announcement/${result.id}`));
      }).catch((xhr) => toast(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Broadcast gagal diterbitkan.', 'bi-exclamation-circle'));
    });
    $('#profileOpen,#profileOpenTop').on('click', () => navigate('profile'));
    $(document).on('submit', '#profilePageForm', function (event) {
      event.preventDefault(); const payload = Object.fromEntries(new FormData(this).entries());
      api('profile', { method: 'PUT', contentType: 'application/json', data: JSON.stringify(payload) }).then((user) => { state.user = user; if (payload.new_password) { state.profileLinkUrl = ''; state.profileLinkExpiresAt = ''; } render(); toast('Profil berhasil diperbarui.'); }).catch((xhr) => toast(xhr.responseJSON?.message || 'Profil belum berhasil disimpan.', 'bi-exclamation-circle'));
    });
    $(document).on('click', '[data-action="generate-profile-link"]', function () {
      api('profile-link', { method: 'POST', contentType: 'application/json', data: JSON.stringify({ action: 'create' }) }).then((result) => { state.profileLinkUrl = result.url; state.profileLinkExpiresAt = result.expires_at; render(); toast('Tautan satu kali dibuat. Salin dan simpan di perangkat pribadimu.', 'bi-shield-lock'); }).catch((xhr) => toast(xhr.responseJSON?.message || 'Tautan tidak berhasil dibuat.', 'bi-exclamation-circle'));
    });
    $(document).on('click', '[data-action="revoke-profile-link"]', function () {
      api('profile-link', { method: 'POST', contentType: 'application/json', data: JSON.stringify({ action: 'revoke' }) }).then(() => { state.profileLinkUrl = ''; state.profileLinkExpiresAt = ''; render(); toast('Tautan aktif sudah dicabut.'); }).catch((xhr) => toast(xhr.responseJSON?.message || 'Tautan tidak berhasil dicabut.', 'bi-exclamation-circle'));
    });
    $(document).on('click', '[data-action="copy-profile-link"]', async function () {
      try { await navigator.clipboard.writeText(state.profileLinkUrl); toast('Tautan masuk cepat disalin.', 'bi-link-45deg'); }
      catch (error) { window.prompt('Salin tautan masuk rahasia ini:', state.profileLinkUrl); }
    });
    $('#profileForm').on('submit', function (event) {
      event.preventDefault(); const payload = Object.fromEntries(new FormData(this).entries());
      api('profile', { method: 'PUT', contentType: 'application/json', data: JSON.stringify(payload) }).then((user) => { state.user = user; this.reset(); render(); modal('#profileModal').hide(); toast('Profil berhasil diperbarui.'); }).catch((xhr) => toast(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Profil belum berhasil disimpan.', 'bi-exclamation-circle'));
    });
    $('#logoutButton').on('click', () => { const form = $('<form method="post"></form>').attr('action', `${document.body.dataset.baseUrl}index.php/auth/logout`); $('body').append(form); form.trigger('submit'); });
    $('#sidebarOpen').on('click', () => { $('#sidebar').addClass('open'); $('#sidebarOverlay').addClass('show'); });
    $('#sidebarClose, #sidebarOverlay').on('click', () => { $('#sidebar').removeClass('open'); $('#sidebarOverlay').removeClass('show'); });
    $('#themeToggle').on('click', function () { const dark = $('body').toggleClass('dark-mode').hasClass('dark-mode'); localStorage.setItem(storageTheme, dark ? 'dark' : 'light'); $(this).find('i').attr('class', dark ? 'bi bi-sun' : 'bi bi-moon-stars'); });
    $(document).on('keydown', (event) => { if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); $('#globalSearch').trigger('focus'); } });
  });
})(jQuery);
