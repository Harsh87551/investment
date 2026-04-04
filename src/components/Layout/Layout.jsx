import React, { useState, useEffect } from 'react';
import { Outlet, useNavigate } from 'react-router-dom';
import API from '../../config/api';
import './Layout.css';

export default function Layout() {
  const navigate = useNavigate();
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [user, setUser] = useState(() => {
    // Initialize from localStorage immediately so profile shows correct data on mount
    const stored = localStorage.getItem('registeredUser');
    if (stored) {
      try {
        const parsed = JSON.parse(stored);
        const { password, ...safeUser } = parsed;
        return safeUser;
      } catch { return null; }
    }
    return null;
  });
  const [profileLoading, setProfileLoading] = useState(true);
  const [imgError, setImgError] = useState(false);

  useEffect(() => {
    const token = localStorage.getItem('token');
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    if (isLoggedIn !== 'true' || !token) {
      navigate('/login');
      return;
    }

    // Fetch user profile from backend using JWT token
    const fetchProfile = async () => {
      try {
        const res = await API.post('/auth.php?action=profile', {}, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        const data = res.data;
        if (data.success && data.user) {
          setUser(data.user);
          // Also store in localStorage as cache
          localStorage.setItem('registeredUser', JSON.stringify(data.user));
        } else {
          // Fallback: try localStorage
          loadUserFromStorage();
        }
      } catch (error) {
        console.error('Error fetching profile:', error);
        // Fallback to localStorage
        loadUserFromStorage();
      } finally {
        setProfileLoading(false);
      }
    };

    const loadUserFromStorage = () => {
      const storedUser = localStorage.getItem('registeredUser');
      if (storedUser) {
        try {
          const parsed = JSON.parse(storedUser);
          const { password, ...safeUser } = parsed;
          setUser(safeUser);
        } catch (e) {
          console.error('Error parsing stored user:', e);
        }
      }
    };

    fetchProfile();
  }, [navigate]);

  const handleLogout = () => {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('token');
    localStorage.removeItem('registeredUser');
    localStorage.removeItem('userProfilePic');
    navigate('/login');
  };

  // Generate initials for avatar fallback
  const getInitials = () => {
    if (!user?.name) return '?';
    return user.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
  };

  return (
    <div className="app-layout">
      {/* Shared Navbar */}
      <nav className="layout-nav">
        <h1 className="layout-title" onClick={() => navigate('/dashboard')}>Investment Plans</h1>
        <div className="hamburger" onClick={() => setIsMenuOpen(!isMenuOpen)}>
          <div className={`bar ${isMenuOpen ? 'open' : ''}`}></div>
          <div className={`bar ${isMenuOpen ? 'open' : ''}`}></div>
          <div className={`bar ${isMenuOpen ? 'open' : ''}`}></div>
        </div>
      </nav>

      {/* Backdrop overlay */}
      {isMenuOpen && <div className="sidebar-backdrop" onClick={() => setIsMenuOpen(false)} />}

      {/* Shared Profile Sidebar */}
      <div className={`profile-menu ${isMenuOpen ? 'active' : ''}`}>
        <div className="menu-header">
          <h3>Profile</h3>
          <button className="close-btn" onClick={() => setIsMenuOpen(false)}>&times;</button>
        </div>
        <div className="menu-content">
          <div className="profile-image-container" onClick={() => document.getElementById('profile-pic-input').click()} style={{ cursor: 'pointer', position: 'relative' }}>
            {user?.profile_pic && !imgError ? (
              <img 
                src={`/backend/${user.profile_pic}`}
                alt="Profile" 
                className="profile-image"
                onError={() => setImgError(true)}
              />
            ) : (
              <div className="profile-initials">{getInitials()}</div>
            )}
            <div className="profile-edit-overlay">
              <span style={{color: "#fff"}}>✎</span>
            </div>
            <input 
              type="file" 
              id="profile-pic-input" 
              accept="image/png,image/jpeg,image/jpg"
              style={{ display: 'none' }}
              onChange={async (e) => {
                const file = e.target.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append('profilePic', file);
                try {
                  const res = await fetch('/backend/update_profile_pic.php', {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` },
                    body: formData
                  });
                  const data = await res.json();
                  if (data.success) {
                    setImgError(false);
                    setUser(prev => ({ ...prev, profile_pic: data.profile_pic }));
                    const stored = localStorage.getItem('registeredUser');
                    if (stored) {
                      const parsed = JSON.parse(stored);
                      parsed.profile_pic = data.profile_pic;
                      localStorage.setItem('registeredUser', JSON.stringify(parsed));
                    }
                  } else {
                    alert(data.message || 'Failed to update picture.');
                  }
                } catch (err) {
                  console.error('Upload error:', err);
                  alert('Failed to upload picture.');
                }
                e.target.value = '';
              }}
            />
          </div>
          {profileLoading ? (
            <div className="profile-loading">
              <div className="profile-skeleton"></div>
              <div className="profile-skeleton short"></div>
              <div className="profile-skeleton"></div>
            </div>
          ) : (
            <>
              <p><strong>Name:</strong> {user?.name || 'User'}</p>
              <p><strong>Email:</strong> {user?.email || 'Not available'}</p>
              <p><strong>Phone:</strong> {user?.phone || 'Not available'}</p>
            </>
          )}
        </div>
        <div className="menu-footer">
          {user?.is_admin && (
            <button 
              className="admin-btn" 
              onClick={() => window.location.href = '/admin/'}
            >
              <span className="admin-icon">⚙️</span> Admin Dashboard
            </button>
          )}
          <button className="logout-btn" onClick={handleLogout}>Log Out</button>
        </div>
      </div>

      {/* Main Page Content */}
      <main className="layout-content">
        <Outlet />
      </main>
    </div>
  );
}

