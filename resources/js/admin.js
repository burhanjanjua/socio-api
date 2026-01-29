const adminApp = document.getElementById('admin-app');

if (adminApp) {
    const API_BASE = '/api';
    const STORAGE_KEY = 'socio_admin_token';

    const elements = {
        loginPanel: document.getElementById('loginPanel'),
        loginForm: document.getElementById('loginForm'),
        loginEmail: document.getElementById('loginEmail'),
        loginPassword: document.getElementById('loginPassword'),
        postsPanel: document.getElementById('postsPanel'),
        postsTable: document.getElementById('postsTable'),
        postsCount: document.getElementById('postsCount'),
        postsPage: document.getElementById('postsPage'),
        prevPageBtn: document.getElementById('prevPageBtn'),
        nextPageBtn: document.getElementById('nextPageBtn'),
        refreshBtn: document.getElementById('refreshBtn'),
        newPostBtn: document.getElementById('newPostBtn'),
        logoutBtn: document.getElementById('logoutBtn'),
        sessionBadge: document.getElementById('sessionBadge'),
        postForm: document.getElementById('postForm'),
        postId: document.getElementById('postId'),
        authorId: document.getElementById('authorId'),
        postTitle: document.getElementById('postTitle'),
        postExcerpt: document.getElementById('postExcerpt'),
        postBody: document.getElementById('postBody'),
        postPublishedAt: document.getElementById('postPublishedAt'),
        publishMode: document.getElementById('publishMode'),
        postImage: document.getElementById('postImage'),
        imagePreview: document.getElementById('imagePreview'),
        savePostBtn: document.getElementById('savePostBtn'),
        cancelEditBtn: document.getElementById('cancelEditBtn'),
        resetFormBtn: document.getElementById('resetFormBtn'),
        editorTitle: document.getElementById('editorTitle'),
        editorSubtitle: document.getElementById('editorSubtitle'),
        formHint: document.getElementById('formHint'),
        toast: document.getElementById('toast'),
    };

    const state = {
        token: localStorage.getItem(STORAGE_KEY) || '',
        page: 1,
        lastPage: 1,
        posts: [],
        editingId: null,
        imageObjectUrl: null,
        userLabel: '',
    };

    const escapeHtml = (value) => {
        const text = String(value ?? '');
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    const formatDate = (value) => {
        if (!value) return 'Not scheduled';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;
        return date.toLocaleString();
    };

    const toDatetimeLocal = (value) => {
        if (!value) return '';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '';
        const pad = (number) => String(number).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(
            date.getMinutes(),
        )}`;
    };

    const showToast = (message, type = 'info') => {
        if (!elements.toast) return;
        elements.toast.textContent = message;
        elements.toast.dataset.type = type;
        elements.toast.classList.add('is-visible');
        clearTimeout(elements.toast._timer);
        elements.toast._timer = setTimeout(() => {
            elements.toast.classList.remove('is-visible');
        }, 3600);
    };

    const setSessionBadge = () => {
        if (!elements.sessionBadge) return;
        if (state.token) {
            elements.sessionBadge.textContent = state.userLabel || 'Session active';
            elements.sessionBadge.dataset.state = 'on';
        } else {
            elements.sessionBadge.textContent = 'Signed out';
            elements.sessionBadge.dataset.state = 'off';
        }
    };

    const setAuthenticated = (isAuthenticated) => {
        elements.loginPanel.hidden = isAuthenticated;
        elements.postsPanel.hidden = !isAuthenticated;
        elements.logoutBtn.disabled = !isAuthenticated;
        setSessionBadge();
    };

    const setToken = (token) => {
        state.token = token || '';
        if (state.token) {
            localStorage.setItem(STORAGE_KEY, state.token);
        } else {
            localStorage.removeItem(STORAGE_KEY);
        }
        setSessionBadge();
    };

    const apiFetch = async (path, options = {}) => {
        const config = {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
            ...options,
        };

        if (state.token) {
            config.headers.Authorization = `Bearer ${state.token}`;
        }

        if (config.body && !(config.body instanceof FormData)) {
            config.headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(`${API_BASE}${path}`, config);
        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json') ? await response.json() : null;

        if (!response.ok) {
            const error = new Error(data?.message || 'Request failed');
            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    };

    const updatePublishMode = (value) => {
        if (!elements.publishMode || !elements.postPublishedAt) return;
        if (value === 'draft') {
            elements.postPublishedAt.value = '';
            elements.postPublishedAt.disabled = true;
            elements.postPublishedAt.required = false;
        } else {
            elements.postPublishedAt.disabled = false;
            elements.postPublishedAt.required = true;
        }
    };

    const resetImagePreview = (content) => {
        if (state.imageObjectUrl) {
            URL.revokeObjectURL(state.imageObjectUrl);
            state.imageObjectUrl = null;
        }
        elements.imagePreview.innerHTML = content || '<span class="muted">No image selected.</span>';
    };

    const resetForm = () => {
        elements.postForm.reset();
        elements.postId.value = '';
        state.editingId = null;
        elements.editorTitle.textContent = 'Create post';
        elements.editorSubtitle.textContent = 'Draft a new post or publish immediately.';
        elements.savePostBtn.textContent = 'Save post';
        elements.cancelEditBtn.disabled = true;
        elements.publishMode.value = 'draft';
        updatePublishMode('draft');
        resetImagePreview();
    };

    const buildFormData = () => {
        const data = new FormData();
        const authorId = elements.authorId.value.trim();
        if (authorId) data.append('author_id', authorId);
        data.append('title', elements.postTitle.value.trim());
        data.append('excerpt', elements.postExcerpt.value.trim());
        data.append('body', elements.postBody.value.trim());

        if (elements.publishMode.value === 'draft') {
            data.append('published_at', '');
        } else if (elements.postPublishedAt.value) {
            data.append('published_at', elements.postPublishedAt.value);
        }

        if (elements.postImage.files[0]) {
            data.append('image', elements.postImage.files[0]);
        }
        return data;
    };

    const renderPosts = () => {
        const posts = state.posts;
        elements.postsTable.innerHTML = '';

        elements.postsCount.textContent = `${posts.length} posts`;
        elements.postsPage.textContent = `Page ${state.page}`;

        if (!posts.length) {
            const empty = document.createElement('div');
            empty.className = 'empty-state';
            empty.innerHTML = '<p>No posts yet. Create your first admin post.</p>';
            elements.postsTable.appendChild(empty);
            return;
        }

        posts.forEach((post) => {
            const row = document.createElement('div');
            row.className = 'post-row';
            row.innerHTML = `
                <div class="post-main">
                    <p class="post-title">${escapeHtml(post.title)}</p>
                    <div class="post-meta">
                        <span>${escapeHtml(post.author?.name || 'Unknown author')}</span>
                        <span class="dot">|</span>
                        <span>${post.is_published ? 'Published' : 'Draft'}</span>
                        <span class="dot">|</span>
                        <span>${escapeHtml(formatDate(post.published_at || post.created_at))}</span>
                    </div>
                </div>
                <div class="post-actions">
                    <button class="btn btn-ghost" data-action="edit" data-id="${post.id}" type="button">Edit</button>
                    <button class="btn btn-danger" data-action="delete" data-id="${post.id}" type="button">Delete</button>
                </div>
            `;
            elements.postsTable.appendChild(row);
        });
    };

    const loadPosts = async (page = 1) => {
        state.page = page;
        elements.postsTable.dataset.state = 'loading';
        try {
            const data = await apiFetch(`/admin/posts?page=${page}`);
            state.posts = Array.isArray(data.data) ? data.data : [];
            state.page = data.current_page || page;
            state.lastPage = data.last_page || page;
            elements.prevPageBtn.disabled = state.page <= 1;
            elements.nextPageBtn.disabled = state.page >= state.lastPage;
            renderPosts();
        } catch (error) {
            if (error.status === 401 || error.status === 403) {
                setToken('');
                state.userLabel = '';
                setAuthenticated(false);
                showToast('Session expired. Please sign in again.', 'error');
                return;
            }
            showToast(error.message || 'Unable to load posts', 'error');
        } finally {
            elements.postsTable.dataset.state = 'idle';
        }
    };

    const startEdit = (post) => {
        if (!post) return;
        state.editingId = post.id;
        elements.postId.value = post.id;
        elements.authorId.value = post.user_id ?? '';
        elements.postTitle.value = post.title ?? '';
        elements.postExcerpt.value = post.excerpt ?? '';
        elements.postBody.value = post.body ?? '';
        elements.postPublishedAt.value = post.published_at ? toDatetimeLocal(post.published_at) : '';
        elements.publishMode.value = post.published_at ? 'publish' : 'draft';
        updatePublishMode(elements.publishMode.value);
        elements.editorTitle.textContent = 'Edit post';
        elements.editorSubtitle.textContent = `Editing: ${post.title}`;
        elements.savePostBtn.textContent = 'Update post';
        elements.cancelEditBtn.disabled = false;

        if (post.image_url) {
            resetImagePreview(`
                <div class="image-card">
                    <img src="${escapeHtml(post.image_url)}" alt="${escapeHtml(post.title)}" />
                    <span class="muted">Current image</span>
                </div>
            `);
        } else {
            resetImagePreview();
        }

        elements.postImage.value = '';
        elements.postForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const handleLogin = async (event) => {
        event.preventDefault();
        try {
            const data = await apiFetch('/auth/admin/login', {
                method: 'POST',
                body: JSON.stringify({
                    email: elements.loginEmail.value.trim(),
                    password: elements.loginPassword.value,
                }),
            });
            setToken(data.token);
            state.userLabel = data.user?.name ? `Signed in as ${data.user.name}` : 'Session active';
            setAuthenticated(true);
            showToast('Admin session unlocked.', 'success');
            await loadPosts(1);
        } catch (error) {
            const message = error.data?.message || 'Admin login failed.';
            showToast(message, 'error');
        }
    };

    const handleLogout = async () => {
        try {
            if (state.token) {
                await apiFetch('/auth/logout', { method: 'POST' });
            }
        } catch (error) {
            // Ignore failed logout, still clear local token.
        } finally {
            setToken('');
            state.userLabel = '';
            setAuthenticated(false);
            resetForm();
            showToast('Signed out.', 'info');
        }
    };

    const handlePostSubmit = async (event) => {
        event.preventDefault();
        elements.savePostBtn.disabled = true;
        try {
            const formData = buildFormData();
            if (state.editingId) {
                await apiFetch(`/admin/posts/${state.editingId}`, {
                    method: 'PATCH',
                    body: formData,
                });
                showToast('Post updated.', 'success');
            } else {
                await apiFetch('/admin/posts', {
                    method: 'POST',
                    body: formData,
                });
                showToast('Post created.', 'success');
            }
            resetForm();
            await loadPosts(state.page);
        } catch (error) {
            const message = error.data?.message || 'Unable to save post.';
            showToast(message, 'error');
        } finally {
            elements.savePostBtn.disabled = false;
        }
    };

    const handleDelete = async (postId) => {
        if (!postId) return;
        const confirmed = window.confirm('Delete this post? This cannot be undone.');
        if (!confirmed) return;
        try {
            await apiFetch(`/admin/posts/${postId}`, { method: 'DELETE' });
            showToast('Post deleted.', 'success');
            await loadPosts(state.page);
        } catch (error) {
            showToast(error.data?.message || 'Unable to delete post.', 'error');
        }
    };

    const handleImageChange = (event) => {
        const file = event.target.files[0];
        if (!file) {
            resetImagePreview();
            return;
        }
        if (state.imageObjectUrl) {
            URL.revokeObjectURL(state.imageObjectUrl);
        }
        state.imageObjectUrl = URL.createObjectURL(file);
        resetImagePreview(`
            <div class="image-card">
                <img src="${state.imageObjectUrl}" alt="Selected preview" />
                <span class="muted">New upload preview</span>
            </div>
        `);
    };

    const handlePostsClick = (event) => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const postId = Number(button.dataset.id);
        const action = button.dataset.action;
        const post = state.posts.find((item) => item.id === postId);

        if (action === 'edit') {
            startEdit(post);
        }

        if (action === 'delete') {
            handleDelete(postId);
        }
    };

    elements.loginForm.addEventListener('submit', handleLogin);
    elements.logoutBtn.addEventListener('click', handleLogout);
    elements.refreshBtn.addEventListener('click', () => loadPosts(state.page));
    elements.newPostBtn.addEventListener('click', resetForm);
    elements.resetFormBtn.addEventListener('click', resetForm);
    elements.cancelEditBtn.addEventListener('click', resetForm);
    elements.postForm.addEventListener('submit', handlePostSubmit);
    elements.postsTable.addEventListener('click', handlePostsClick);
    elements.prevPageBtn.addEventListener('click', () => loadPosts(state.page - 1));
    elements.nextPageBtn.addEventListener('click', () => loadPosts(state.page + 1));
    elements.postImage.addEventListener('change', handleImageChange);
    elements.publishMode.addEventListener('change', (event) => updatePublishMode(event.target.value));

    elements.cancelEditBtn.disabled = true;
    setSessionBadge();
    updatePublishMode('draft');

    if (state.token) {
        setAuthenticated(true);
        loadPosts(1);
    } else {
        setAuthenticated(false);
    }
}
