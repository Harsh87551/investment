import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import './Payment.css';

export default function Payment() {
  const navigate = useNavigate();
  const location = useLocation();
  const state = location.state || {};
  const strategyName = state.strategy || 'Starter';
  const selectedAmount = state.amount || 5000; // USD
  const reward = state.reward || null;
  const planId = state.plan_id || null;
  const packageId = state.package_id || null;

  const [upiId, setUpiId] = useState('');
  const [txnId, setTxnId] = useState('');
  const [screenshot, setScreenshot] = useState(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [loadingCode, setLoadingCode] = useState(true);
  const [usdToInr, setUsdToInr] = useState(83); // fallback

  const isFormValid = txnId.trim().length >= 6 && screenshot !== null;

  useEffect(() => {

    const fetchUpi = async () => {
      try {
        const res = await fetch('/backend/upi.php');
        const data = await res.json();
        if (data.success) {
          setUpiId(data.upi_id);
        } else {
          setUpiId('harsh.rajput14@ybl');
        }
      } catch {
        setUpiId('harsh.rajput14@ybl');
      }
    };

    const fetchRate = async () => {
      try {
        const res = await fetch('https://open.er-api.com/v6/latest/USD');
        const data = await res.json();

        if (data.result === "success") {
          setUsdToInr(data.rates.INR);
        }
      } catch (err) {
        console.error("Exchange rate fetch error", err);
      } finally {
        setLoadingCode(false);
      }
    };

    fetchUpi();
    fetchRate();

  }, []);

  const generateQRCode = () => {

    const amountInINR = Math.round(selectedAmount * usdToInr);

    const upiUri = `upi://pay?pa=${upiId}&pn=Investment Platform&am=${amountInINR}&cu=INR&tn=Investment Plan`;

    return `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(upiUri)}&bgcolor=FFFFFF&color=0f172a`;
  };

  const handlePay = async () => {
    if (!isFormValid) return;
    setIsProcessing(true);

    try {

      const formData = new FormData();

      formData.append('amount_usd', selectedAmount);
      formData.append('amount_inr', Math.round(selectedAmount * usdToInr));
      formData.append('strategy', strategyName);
      formData.append('reward', reward || '');
      formData.append('txnId', txnId);
      formData.append('screenshot', screenshot);
      formData.append('plan_id', planId || '');
      formData.append('package_id', packageId || '');

      const response = await fetch('/backend/deposit.php', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: formData
      });

      const data = await response.json();

      if (data.success) {
        alert('Payment proof submitted! Your investment is being processed.');
        navigate('/dashboard');
      } else {
        alert('Error: ' + data.message);
      }

    } catch (error) {
      console.error('Payment submission error:', error);
      alert('Failed to submit payment details.');
    } finally {
      setIsProcessing(false);
    }
  };

  const amountINR = Math.round(selectedAmount * usdToInr);

  return (
    <div className="payment-container">

      <div className="payment-card">

        <div className="payment-header">
          <button className="back-btn-simple" onClick={() => navigate('/dashboard')}>
            ← Abandon
          </button>

          <div className="header-text">
            <h2>Verify Payment</h2>
            <p>Upload proof of transaction</p>
          </div>
        </div>

        <div className="payment-content">

          <div className="qr-section">

            <div className={`qr-wrapper ${loadingCode ? 'loading' : ''}`}>
              {loadingCode ? (
                <div className="qr-spinner"></div>
              ) : (
                <img
                  src={generateQRCode()}
                  alt="UPI QR Code"
                  className="payment-qr"
                />
              )}
            </div>

            <p className="upi-id-display">
              UPI: <span>{upiId}</span>
            </p>

            <p style={{marginTop:'10px'}}>
              Pay ₹{amountINR.toLocaleString()} (≈ ${selectedAmount})
            </p>

          </div>

          <div className="summary-box payment-summary">

            <div className="summary-row">
              <span className="label">Selected Strategy</span>
              <span className="value highlight">
                {strategyName} Strategy
              </span>
            </div>

            {reward && (
              <div className="summary-row">
                <span className="label">Your Reward</span>
                <span className="value reward">
                  {reward.replace('🎁 ', '')}
                </span>
              </div>
            )}

            <div className="summary-row total">
              <span className="label">Amount</span>
              <span className="value total">
                ${selectedAmount.toLocaleString()}
              </span>
            </div>

          </div>

          <div className="verification-form">

            <div className="input-group">
              <label>Transaction ID / UTR Number</label>

              <input
                type="text"
                placeholder="Enter UTR Number"
                value={txnId}
                onChange={(e) => setTxnId(e.target.value)}
              />
            </div>

            <div className="input-group upload-proof">

              <label>Upload Payment Screenshot</label>

              <div className={`file-upload-box ${screenshot ? 'has-file' : ''}`}>
                <input
                  type="file"
                  id="screenshot-upload"
                  accept="image/*"
                  onChange={(e) => setScreenshot(e.target.files[0])}
                />

                <label htmlFor="screenshot-upload">
                  <span className="file-icon">
                    {screenshot ? '✅' : '📸'}
                  </span>

                  <span className="file-text">
                    {screenshot ? screenshot.name : 'Choose Screenshot Image'}
                  </span>
                </label>

              </div>

            </div>

          </div>

          <div className="actions">

            <button
              className={`pay-now-btn ${isProcessing ? 'loading' : ''} ${!isFormValid ? 'disabled' : ''}`}
              onClick={handlePay}
              disabled={isProcessing || !isFormValid}
            >
              {isProcessing ? 'Confirming Details...' : 'Submit Payment Proof'}
            </button>

          </div>

        </div>

      </div>

    </div>
  );
}