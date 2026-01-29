<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Socio Admin</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|fraunces:600,700" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="admin-body">
        <div id="admin-app" class="admin-shell">
            <header class="admin-header">
                <div class="admin-brand">
                    <div class="brand-mark">S</div>
                    <div>
                        <p class="brand-label">Socio Admin</p>
                        <h1 class="brand-title">Posts Control Room</h1>
                        <p class="brand-subtitle">Review, publish, and curate posts in one place.</p>
                    </div>
                </div>
                <div class="admin-header-actions">
                    <div id="sessionBadge" class="status-pill">Signed out</div>
                    <button id="logoutBtn" class="btn btn-ghost" type="button">Log out</button>
                </div>
            </header>

            <main class="admin-main">
                <section id="loginPanel" class="panel panel-login">
                    <div class="panel-header">
                        <div>
                            <p class="eyebrow">Admin access</p>
                            <h2>Sign in with your admin account</h2>
                            <p class="muted">Only admins can manage posts in this workspace.</p>
                        </div>
                    </div>
                    <form id="loginForm" class="form-grid">
                        <div class="field">
                            <label for="loginEmail">Email</label>
                            <input id="loginEmail" name="email" type="email" placeholder="admin@socio-api.test" required />
                        </div>
                        <div class="field">
                            <label for="loginPassword">Password</label>
                            <input id="loginPassword" name="password" type="password" placeholder="Enter password" required />
                        </div>
                        <button class="btn btn-primary" type="submit">Unlock admin panel</button>
                        <p class="hint">Need admin access? Create an admin user and log in here.</p>
                    </form>
                </section>

                <section id="postsPanel" class="panel panel-posts" hidden>
                    <div class="panel-header panel-header-split">
                        <div>
                            <p class="eyebrow">Admin posts</p>
                            <h2>Manage every post</h2>
                            <p class="muted">Create, edit, and publish posts across all authors.</p>
                        </div>
                        <div class="panel-actions">
                            <button id="refreshBtn" class="btn btn-ghost" type="button">Refresh</button>
                            <button id="newPostBtn" class="btn btn-primary" type="button">New post</button>
                        </div>
                    </div>

                    <div class="posts-grid">
                        <div class="posts-list">
                            <div class="posts-toolbar">
                                <div class="toolbar-status">
                                    <span id="postsCount">0 posts</span>
                                    <span id="postsPage">Page 1</span>
                                </div>
                                <div class="toolbar-actions">
                                    <button id="prevPageBtn" class="btn btn-ghost" type="button">Prev</button>
                                    <button id="nextPageBtn" class="btn btn-ghost" type="button">Next</button>
                                </div>
                            </div>
                            <div id="postsTable" class="posts-table"></div>
                        </div>

                        <div class="post-editor">
                            <div class="editor-head">
                                <div>
                                    <p class="eyebrow">Editor</p>
                                    <h3 id="editorTitle">Create post</h3>
                                    <p id="editorSubtitle" class="muted">Draft a new post or publish immediately.</p>
                                </div>
                                <button id="cancelEditBtn" class="btn btn-ghost" type="button">Cancel</button>
                            </div>

                            <form id="postForm" class="form-stack">
                                <input id="postId" type="hidden" />
                                <div class="field">
                                    <label for="authorId">Author ID (optional)</label>
                                    <input id="authorId" name="author_id" type="number" min="1" placeholder="Defaults to you" />
                                </div>
                                <div class="field">
                                    <label for="postTitle">Title</label>
                                    <input id="postTitle" name="title" type="text" placeholder="Post title" required />
                                </div>
                                <div class="field">
                                    <label for="postExcerpt">Excerpt</label>
                                    <textarea id="postExcerpt" name="excerpt" rows="3" placeholder="Short summary"></textarea>
                                </div>
                                <div class="field">
                                    <label for="postBody">Body</label>
                                    <textarea id="postBody" name="body" rows="8" placeholder="Write the full post" required></textarea>
                                </div>
                                <div class="field">
                                    <label for="publishMode">Publish status</label>
                                    <select id="publishMode" name="publish_mode">
                                        <option value="draft">Draft</option>
                                        <option value="publish">Set publish date</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="postPublishedAt">Published at</label>
                                    <input id="postPublishedAt" name="published_at" type="datetime-local" />
                                </div>
                                <div class="field">
                                    <label for="postImage">Image</label>
                                    <input id="postImage" name="image" type="file" accept="image/*" />
                                    <div id="imagePreview" class="image-preview">
                                        <span class="muted">No image selected.</span>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button id="savePostBtn" class="btn btn-primary" type="submit">Save post</button>
                                    <button id="resetFormBtn" class="btn btn-ghost" type="button">Reset</button>
                                </div>
                                <p id="formHint" class="hint">Uploads support JPG, PNG, and WebP up to 2MB.</p>
                            </form>
                        </div>
                    </div>
                </section>
            </main>
        </div>

        <div id="toast" class="toast" role="status" aria-live="polite"></div>
    </body>
</html>
