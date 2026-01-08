// Utility: escape HTML to prevent XSS
function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// Real-time comment updates
let commentUpdateInterval;

function startRealTimeCommentUpdates() {
  // Update comments every 5 seconds for real-time experience
  commentUpdateInterval = setInterval(() => {
    document.querySelectorAll('.comment-section').forEach(section => {
      const postId = section.id.split('-')[1];
      const state = commentStates.get(postId);
      
      // Only update if comments are expanded and visible
      if (state && state.expanded) {
        updateCommentsRealTime(postId);
      }
    });
  }, 5000);
}

function updateCommentsRealTime(postId) {
  fetch(`api/fetch_comments.php?post_id=${postId}&limit=50&offset=0`)
    .then(res => res.json())
    .then(data => {
      if (!data.comments || !Array.isArray(data.comments)) return;
      
      const commentsDiv = document.getElementById(`comments-${postId}`);
      const currentComments = commentsDiv.querySelectorAll('.comment').length;
      
      // Only update if there are new comments
      if (data.total > currentComments) {
        const commentsHtml = data.comments.map(c =>
          `<div class="comment">
            <img src="img/${escapeHtml(c.avatar)}" alt="avatar" />
            <div class="comment-content">
              <strong>@${escapeHtml(c.username)}</strong>
              <p>${escapeHtml(c.content)}</p>
              <small>${escapeHtml(c.created_at)}</small>
            </div>
          </div>`
        ).join('');

        commentsDiv.innerHTML = commentsHtml;
        
        // Update comment toggle text
        const commentToggle = document.querySelector(`.comment-toggle[data-post-id="${postId}"]`);
        if (commentToggle) {
          commentToggle.textContent = `💬 Comments (${data.total})`;
        }
      }
    })
    .catch(() => {});
}

// Like button toggle
document.querySelectorAll('.like-btn').forEach(button => {
  button.addEventListener('click', () => {
    const postId = button.getAttribute('data-post-id');
    fetch('api/like_post.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'post_id=' + encodeURIComponent(postId)
    })
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        alert(data.error);
        return;
      }
      if (data.liked) {
        button.classList.add('liked');
      } else {
        button.classList.remove('liked');
      }
      button.querySelector('.like-count').textContent = data.like_count;
    })
    .catch(() => alert('Error liking post'));
  });
});

// Comment system with pagination
const commentStates = new Map();

// Initialize comment count display and show preview
function initializeComments(postId) {
  fetch(`api/fetch_comments.php?post_id=${postId}&limit=1&offset=0`)
    .then(res => res.json())
    .then(data => {
      const commentToggle = document.querySelector(`.comment-toggle[data-post-id="${postId}"]`);

      const commentsDiv = document.getElementById(`comments-${postId}`);
      
      if (commentToggle && data.total !== undefined) {
        commentToggle.textContent = `💬 Comments (${data.total})`;
      }
      
      // Show preview of most recent comment if exists
      if (data.comments && data.comments.length > 0 && data.total > 0) {
        const latestComment = data.comments[0];
        const previewHtml = `
          <div class="comment-preview">
            <img src="img/${escapeHtml(latestComment.avatar)}" alt="avatar" />
            <div class="comment-content">
              <strong>@${escapeHtml(latestComment.username)}</strong>
              <p>${escapeHtml(latestComment.content)}</p>
              <small>${escapeHtml(latestComment.created_at)}</small>
            </div>
          </div>
        `;
        commentsDiv.innerHTML = previewHtml;
        commentsDiv.style.display = 'block';
        
        // Update state
        commentStates.set(postId, {
          loaded: 0,
          total: data.total,
          expanded: false,
          hasPreview: true
        });
      } else {
        commentsDiv.style.display = 'none';
        commentStates.set(postId, {
          loaded: 0,
          total: 0,
          expanded: false,
          hasPreview: false
        });
      }
    })
    .catch(() => {});
}

// Load comments for a given post with pagination
function loadComments(postId, offset = 0, append = false) {
  const commentsDiv = document.getElementById(`comments-${postId}`);
  const limit = 10;
  
  if (!append) {
    commentsDiv.innerHTML = '<div class="loading">Loading comments...</div>';
  }
  
  fetch(`api/fetch_comments.php?post_id=${postId}&limit=${limit}&offset=${offset}`)
    .then(res => res.json())
    .then(data => {
      if (!data.comments || !Array.isArray(data.comments)) return;
      
      const commentsHtml = data.comments.map(c =>
        `<div class="comment">
          <img src="img/${escapeHtml(c.avatar)}" alt="avatar" />
          <div class="comment-content">
            <strong>@${escapeHtml(c.username)}</strong>
            <p>${escapeHtml(c.content)}</p>
            <small>${escapeHtml(c.created_at)}</small>
          </div>
        </div>`
      ).join('');

      if (append) {
        const loadingDiv = commentsDiv.querySelector('.loading');
        if (loadingDiv) loadingDiv.remove();
        
        const showMoreBtn = commentsDiv.querySelector('.show-more-btn');
        if (showMoreBtn) showMoreBtn.remove();
        
        commentsDiv.insertAdjacentHTML('beforeend', commentsHtml);
      } else {
        commentsDiv.innerHTML = commentsHtml;
      }

      // Add "Show more" button if there are more comments
      if (data.has_more) {
        const showMoreBtn = document.createElement('button');
        showMoreBtn.className = 'show-more-btn';
        showMoreBtn.textContent = 'Show more comments';
        showMoreBtn.onclick = () => {
          showMoreBtn.innerHTML = '<div class="loading">Loading...</div>';
          loadComments(postId, offset + limit, true);
        };
        commentsDiv.appendChild(showMoreBtn);
      }

      // Update comment state
      commentStates.set(postId, {
        loaded: offset + data.comments.length,
        total: data.total,
        expanded: true,
        hasPreview: false
      });

      // Update comment toggle text
      const commentToggle = document.querySelector(`.comment-toggle[data-post-id="${postId}"]`);
      if (commentToggle) {
        commentToggle.textContent = `💬 Comments (${data.total})`;
      }
    })
    .catch(() => {
      commentsDiv.innerHTML = '<p style="color:red;">Failed to load comments.</p>';
    });
}

// Toggle comment section visibility
function toggleComments(postId) {
  const commentsDiv = document.getElementById(`comments-${postId}`);
  const state = commentStates.get(postId) || { expanded: false, loaded: 0, hasPreview: false };
  
  if (!state.expanded) {
    commentsDiv.style.display = 'block';
    // Load full comments if not already loaded or if only preview is shown
    if (state.loaded === 0 || state.hasPreview) {
      loadComments(postId, 0, false);
    }
    commentStates.set(postId, { ...state, expanded: true });
  } else {
    // Go back to preview or hide completely
    if (state.total > 0) {
      // Show preview again
      fetch(`api/fetch_comments.php?post_id=${postId}&limit=1&offset=0`)
        .then(res => res.json())
        .then(data => {
          if (data.comments && data.comments.length > 0) {
            const latestComment = data.comments[0];
            const previewHtml = `
              <div class="comment-preview">
                <img src="img/${escapeHtml(latestComment.avatar)}" alt="avatar" />
                <div class="comment-content">
                  <strong>@${escapeHtml(latestComment.username)}</strong>
                  <p>${escapeHtml(latestComment.content)}</p>
                  <small>${escapeHtml(latestComment.created_at)}</small>
                </div>
              </div>
            `;
            commentsDiv.innerHTML = previewHtml;
            commentStates.set(postId, { ...state, expanded: false, hasPreview: true });
          }
        });
    } else {
      commentsDiv.style.display = 'none';
      commentStates.set(postId, { ...state, expanded: false });
    }
  }
}

// Initialize comments on page load
document.querySelectorAll('.comment-section').forEach(section => {
  const postId = section.id.split('-')[1];
  initializeComments(postId);
  
  // Add click handler to comment toggle
  const commentToggle = document.querySelector(`.comment-toggle[data-post-id="${postId}"]`);
  if (commentToggle) {
    commentToggle.addEventListener('click', (e) => {
      e.preventDefault();
      toggleComments(postId);
    });
  }
});

// Handle new comment submission with real-time update
document.querySelectorAll('.comment-form').forEach(form => {
  form.addEventListener('submit', e => {
    e.preventDefault();
    const postId = form.dataset.postId;
    const input = form.querySelector('input[name="content"]');
    const content = input.value.trim();
    if (!content) return;

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;

    fetch('api/comment_post.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `post_id=${encodeURIComponent(postId)}&content=${encodeURIComponent(content)}`
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        input.value = '';
        
        // Add the new comment immediately for real-time feel
        const commentsDiv = document.getElementById(`comments-${postId}`);
        const state = commentStates.get(postId) || {};
        
        if (state.expanded) {
          // Add to expanded view
          const newCommentHtml = `
            <div class="comment">
              <img src="img/${escapeHtml(data.comment.avatar)}" alt="avatar" />
              <div class="comment-content">
                <strong>@${escapeHtml(data.comment.username)}</strong>
                <p>${escapeHtml(data.comment.content)}</p>
                <small>${escapeHtml(data.comment.created_at)}</small>
              </div>
            </div>
          `;
          commentsDiv.insertAdjacentHTML('afterbegin', newCommentHtml);
        } else {
          // Update preview
          const previewHtml = `
            <div class="comment-preview">
              <img src="img/${escapeHtml(data.comment.avatar)}" alt="avatar" />
              <div class="comment-content">
                <strong>@${escapeHtml(data.comment.username)}</strong>
                <p>${escapeHtml(data.comment.content)}</p>
                <small>${escapeHtml(data.comment.created_at)}</small>
              </div>
            </div>
          `;
          commentsDiv.innerHTML = previewHtml;
          commentsDiv.style.display = 'block';
        }
        
        // Update comment count
        const newTotal = (state.total || 0) + 1;
        commentStates.set(postId, { ...state, total: newTotal, hasPreview: true });
        
        const commentToggle = document.querySelector(`.comment-toggle[data-post-id="${postId}"]`);
        if (commentToggle) {
          commentToggle.textContent = `💬 Comments (${newTotal})`;
        }
      } else {
        alert(data.error || 'Failed to add comment');
      }
    })
    .catch(() => alert('Network error adding comment'))
    .finally(() => {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    });
  });
});

// Start real-time updates
if (document.querySelectorAll('.comment-section').length > 0) {
  startRealTimeCommentUpdates();
}

// Clean up interval when page unloads
window.addEventListener('beforeunload', () => {
  if (commentUpdateInterval) {
    clearInterval(commentUpdateInterval);
  }
});

// Notification badge update
function updateNotificationBadge() {
  fetch('api/fetch_notifications.php')
    .then(res => res.json())
    .then(data => {
      const badge = document.querySelector('.navbar__badge');
      if (Array.isArray(data) && data.length > 0) {
        if (badge) {
          badge.textContent = data.length;
          badge.style.display = 'block';
        }
      } else {
        if (badge) {
          badge.style.display = 'none';
        }
      }
    })
    .catch(() => {});
}

// Update notification badge on page load and periodically
updateNotificationBadge();
setInterval(updateNotificationBadge, 30000); // Check every 30 seconds

// Password visibility toggle
const passwordInput = document.getElementById('password');
if (passwordInput) {
  const toggleContainer = document.createElement('div');
  toggleContainer.className = 'password-toggle-container';
  
  const toggleBtn = document.createElement('button');
  toggleBtn.type = 'button';
  toggleBtn.className = 'password-toggle';
  toggleBtn.innerHTML = '👁️';
  toggleBtn.setAttribute('aria-label', 'Toggle password visibility');
  
  passwordInput.parentNode.appendChild(toggleContainer);
  toggleContainer.appendChild(passwordInput);
  toggleContainer.appendChild(toggleBtn);
  
  toggleBtn.addEventListener('click', () => {
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      toggleBtn.innerHTML = '🙈';
      toggleBtn.setAttribute('aria-label', 'Hide password');
    } else {
      passwordInput.type = 'password';
      toggleBtn.innerHTML = '👁️';
      toggleBtn.setAttribute('aria-label', 'Show password');
    }
  });
}