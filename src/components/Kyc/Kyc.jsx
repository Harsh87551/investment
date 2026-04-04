import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './Kyc.css';

export default function Kyc() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    aadhaarNumber: '',
    panNumber: ''
  });
  const [aadhaarPic, setAadhaarPic] = useState(null);
  const [panPic, setPanPic] = useState(null);
  const [profilePic, setProfilePic] = useState(null);
  const [previewPic, setPreviewPic] = useState(null);

  const handleTextChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  const handleFileChange = (e, setter, isProfile = false) => {
    const file = e.target.files[0];
    if (file) {
      setter(file);
      if (isProfile) {
        const reader = new FileReader();
        reader.onloadend = () => {
          setPreviewPic(reader.result);
        };
        reader.readAsDataURL(file);
      }
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Check if files are selected
    if (!aadhaarPic || !panPic || !profilePic) {
      alert('Please upload all required documents.');
      return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
      alert('Session expired. Please login again.');
      navigate('/login');
      return;
    }

    const formDataObj = new FormData();
    formDataObj.append('aadharNumber', formData.aadhaarNumber);
    formDataObj.append('panNumber', formData.panNumber);
    formDataObj.append('aadharPic', aadhaarPic);
    formDataObj.append('panPic', panPic);
    formDataObj.append('profilePic', profilePic);

    try {
      const response = await fetch('/backend/kyc.php', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formDataObj
      });

      const data = await response.json();
      
      if (data.success) {
        // Save profile picture URL
        if (data.profilePic) {
          localStorage.setItem('userProfilePic', data.profilePic);
          window.dispatchEvent(new Event('profileUpdated'));
        }
        // Mark KYC as completed
        localStorage.setItem('kyc_completed', 'true');
        alert('KYC Verified & Profile Updated Successfully!');
        navigate('/dashboard');
      } else {
        alert('Error: ' + data.message);
      }
    } catch (error) {
      console.error('KYC submission error:', error);
      alert('Failed to submit KYC. Please try again later.');
    }
  };

  return (
    <div className="kyc-container">
      <div className="kyc-bg-glow glow-1"></div>
      <div className="kyc-bg-glow glow-2"></div>
      
      <div className="kyc-card">
        <div className="kyc-header">
          <button type="button" className="kyc-back-btn" onClick={() => navigate('/dashboard')}>← Back</button>
          <h2>KYC Verification</h2>
        </div>
        <p className="kyc-subtitle">Secure your account by completing KYC to activate your plan.</p>
        
        <form onSubmit={handleSubmit} className="kyc-form">
          
          <div className="form-section">
            <h3>1. Document Information</h3>
            <div className="grid-group">
              <div className="input-wrapper">
                <label>Aadhaar Number</label>
                <input 
                  type="text" 
                  name="aadhaarNumber" 
                  placeholder="0000 0000 0000" 
                  value={formData.aadhaarNumber} 
                  onChange={handleTextChange}
                  required 
                  maxLength={12}
                />
              </div>
              <div className="input-wrapper">
                <label>PAN Number</label>
                <input 
                  type="text" 
                  name="panNumber" 
                  placeholder="ABCDE1234F" 
                  value={formData.panNumber} 
                  onChange={handleTextChange}
                  required 
                  maxLength={10}
                />
              </div>
            </div>
          </div>

          <div className="form-section">
            <h3>2. Document Uploads</h3>
            
            <div className="upload-grid">
              {/* Aadhaar Upload Box */}
              <div className={`upload-box ${aadhaarPic ? 'has-file' : ''}`}>
                <input 
                  type="file" 
                  accept="image/*" 
                  id="aadhaar-upload"
                  onChange={(e) => handleFileChange(e, setAadhaarPic)} 
                  required 
                />
                <label htmlFor="aadhaar-upload">
                  <span className="upload-icon">{aadhaarPic ? '✅' : '📄'}</span>
                  <span className="upload-text">
                    {aadhaarPic ? aadhaarPic.name : 'Upload Aadhaar Card'}
                  </span>
                </label>
              </div>

              {/* PAN Upload Box */}
              <div className={`upload-box ${panPic ? 'has-file' : ''}`}>
                <input 
                  type="file" 
                  accept="image/*" 
                  id="pan-upload"
                  onChange={(e) => handleFileChange(e, setPanPic)} 
                  required 
                />
                <label htmlFor="pan-upload">
                  <span className="upload-icon">{panPic ? '✅' : '💳'}</span>
                  <span className="upload-text">
                    {panPic ? panPic.name : 'Upload PAN Card'}
                  </span>
                </label>
              </div>

              {/* Personal Photo Upload Box */}
              <div className={`upload-box profile-upload ${profilePic ? 'has-file' : ''}`}>
                <input 
                  type="file" 
                  accept="image/*" 
                  id="profile-upload"
                  onChange={(e) => handleFileChange(e, setProfilePic, true)} 
                  required 
                />
                <label htmlFor="profile-upload">
                  {previewPic ? (
                    <>
                      <div className="pic-preview">
                        <img src={previewPic} alt="Live Capture" />
                      </div>
                      <span className="upload-text success-text">Profile Photo Verified</span>
                    </>
                  ) : (
                    <>
                      <span className="upload-icon">📸</span>
                      <span className="upload-text">Take / Upload Live Selfie</span>
                      <span className="sub-text">Used for facial verification</span>
                    </>
                  )}
                </label>
              </div>
            </div>
          </div>

          <button type="submit" className="kyc-submit-btn">
            Submit Identity <span className="arrow">→</span>
          </button>
        </form>
      </div>
    </div>
  );
}
