import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import './Dashboard.css';

// Metadata for packages (durations/strategies) will be fetched from backend
let planDetails = {};

export default function Dashboard() {
  const navigate = useNavigate();
  const [selectedAmount, setSelectedAmount] = useState(5000);
  const [plans, setPlans] = useState([]);
  const [selectedPlan, setSelectedPlan] = useState('monthly');
  const [packages, setPackages] = useState([]);
  const [kycStatus, setKycStatus] = useState(0);
  const [investments, setInvestments] = useState([]);
  const [showPlans, setShowPlans] = useState(false);
  const [loading, setLoading] = useState(true);
  const [userProfile, setUserProfile] = useState({});

  // States for Withdrawal Modal
  const [showWithdrawModal, setShowWithdrawModal] = useState(false);
  const [withdrawId, setWithdrawId] = useState(null);
  const [selectedInv, setSelectedInv] = useState(null);
  const [bankData, setBankData] = useState({
    bankName: '',
    account: '',
    ifsc: '',
    saveToProfile: false,
    agreedToFee: false,
    agreedToTime: false
  });
  const [isSubmittingWithdraw, setIsSubmittingWithdraw] = useState(false);

  // Derived state for verified status
  const isKycDone = kycStatus === 2;
  useEffect(() => {
    const fetchAllData = async () => {
      try {
        const [plansRes, packagesRes, kycRes, historyRes, profileRes] = await Promise.all([
          fetch('/backend/plan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'getPlans' })
          }),
          fetch('/backend/package.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'getPackages' })
          }),
          fetch('/backend/kyc.php', {
            method: 'POST',
            headers: { 
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify({ action: 'getStatus' })
          }),
          fetch('/backend/history.php', {
            method: 'POST',
            headers: { 
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify({ action: 'getHistory' })
          }),
          fetch('/backend/auth.php?action=profile', {
            method: 'GET',
            headers: { 
              'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
          })
        ]);
 
        const [plansData, packagesData, kycResData, historyResData, profileData] = await Promise.all([
          plansRes.json(),
          packagesRes.json(),
          kycRes.json(),
          historyRes.json(),
          profileRes.json()
        ]);

        if (profileData.success) {
          setUserProfile(profileData.user);
          // Pre-fill bank details if they exist
          setBankData(prev => ({
            ...prev,
            bankName: profileData.user.bankname || '',
            account: profileData.user.account || '',
            ifsc: profileData.user.ifsc_code || ''
          }));
        }

        if (kycResData.success) {
          setKycStatus(kycResData.status);
        }

        if (historyResData.success) {
          setInvestments(historyResData.history);
        }

        if (plansData.success) {
          const sortedPlans = plansData.plans.sort((a, b) => a.investment - b.investment);
          setPlans(sortedPlans);
          if (sortedPlans.length > 0) {
            const has5k = sortedPlans.find(p => p.investment === 5000);
            setSelectedAmount(has5k ? 5000 : sortedPlans[0].investment);
          }
        }

        if (packagesData.success) {
          setPackages(packagesData.packages);
          // Build planDetails mapping for backward compatibility with calculations
          const mapping = {};
          packagesData.packages.forEach(pkg => {
            mapping[pkg.key] = {
              title: pkg.name.charAt(0).toUpperCase() + pkg.name.slice(1) + (pkg.name.includes('Plan') ? '' : ' Plan'),
              strategy: pkg.strategy,
              reward: pkg.trip
            };
          });
          planDetails = mapping; // Update outer scope
          if (packagesData.packages.length > 0 && !packagesData.packages.find(p => p.key === 'monthly')) {
             setSelectedPlan(packagesData.packages[0].key);
          }
        }
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchAllData();
  }, []);

  const isKycProcessing = kycStatus === 1;
  const isKycRejected = kycStatus === 3;


  const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
      minimumFractionDigits: val % 1 === 0 ? 0 : 2,
      maximumFractionDigits: 2
    }).format(val);
  };

  const currentData = plans.find(row => row.investment === selectedAmount) || plans[0] || {
    investment: selectedAmount,
    returns: 0,
    monthly: 0,
    sixMonth: 0,
    nineMonth: 0,
    twelveMonth: 0
  };

  const getProfitValue = () => {
    switch (selectedPlan) {
      case 'monthly': return currentData.monthly;
      case '6month': return currentData.sixMonth;
      case '9month': return currentData.nineMonth;
      case '12month': return currentData.twelveMonth;
      default: return currentData.monthly;
    }
  };

  const handleProceed = () => {
    const selectedPlanData = planDetails[selectedPlan];
    const strategyName = selectedPlanData.strategy;
    const reward = selectedPlanData.reward;

    // Get the specific IDs for DB mapping
    const currentPlan = plans.find(p => p.investment === selectedAmount);
    const currentPackage = packages.find(pkg => pkg.key === selectedPlan);

    navigate('/payment', { 
      state: { 
        amount: selectedAmount, 
        strategy: strategyName,
        reward: reward,
        plan_id: currentPlan ? currentPlan.id : null,
        package_id: currentPackage ? currentPackage.id : null
      } 
    });
  };

  const handleWithdraw = (inv) => {
    setSelectedInv(inv);
    setWithdrawId(inv.id);
    setShowWithdrawModal(true);
    
    // Reset agreements
    setBankData(prev => ({
      ...prev,
      agreedToFee: false,
      agreedToTime: false
    }));
  };

  const confirmWithdrawal = async () => {
    if (!bankData.bankName || !bankData.account || !bankData.ifsc) {
      alert("Please enter all bank details.");
      return;
    }
    
    if (!bankData.agreedToFee) {
      alert("Please agree to the withdrawal fee to proceed.");
      return;
    }

    if (!bankData.agreedToTime) {
      alert("Please acknowledge the 24-48 hour processing time.");
      return;
    }

    setIsSubmittingWithdraw(true);
    try {
      const res = await fetch('/backend/withdraw.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({ 
          action: 'request', 
          deposit_id: withdrawId, 
          method: 'bank',
          bankname: bankData.bankName,
          account: bankData.account,
          ifsc: bankData.ifsc,
          save_to_profile: bankData.saveToProfile
        })
      });
      const data = await res.json();
      if (data.success) {
        setShowWithdrawModal(false);
        alert('✅ ' + data.message);
        // Refresh investments
        const historyRes = await fetch('/backend/history.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          },
          body: JSON.stringify({ action: 'getHistory' })
        });
        const historyData = await historyRes.json();
        if (historyData.success) setInvestments(historyData.history);
      } else {
        alert('⚠️ ' + (data.message || 'Failed to request withdrawal.'));
      }
    } catch (err) {
      console.error('Withdraw error:', err);
      alert('Failed to submit withdrawal request.');
    } finally {
      setIsSubmittingWithdraw(false);
    }
  };


  return (
    <main className="dashboard-content">
      {loading ? (
        <div className="loading-state">
          <div className="spinner"></div>
          <p>Loading Investment Plans...</p>
        </div>
      ) : !showPlans ? (
        <div className={investments.length === 0 ? "empty-with-rewards" : "history-view"}>
          <div className="kyc-home-banner" onClick={(!isKycDone && !isKycProcessing) || isKycRejected ? () => navigate('/kyc') : undefined}>
            <div className="kyc-info">
              <h3>{isKycDone ? 'Account Verified' : isKycProcessing ? 'Verification Under Review' : isKycRejected ? 'Verification Rejected' : 'Verification Pending'}</h3>
              <p>
                {isKycDone 
                  ? 'Your identity has been successfully verified.' 
                  : isKycProcessing 
                    ? 'Our team is verifying your documents. This usually takes 24-48 hours.' 
                    : isKycRejected 
                      ? 'Your verification has been rejected. Please update your documents and try again.' 
                      : 'Complete your KYC to enjoy full platform benefits and higher limits.'}
              </p>
            </div>
            {isKycDone ? (
              <div className="verified-badge-status">
                <span>Verified</span>
                <div className="verified-tick-main">✔️</div>
              </div>
            ) : isKycProcessing ? (
              <div className="verified-badge-status processing">
                <div className="processing-dot"></div>
                <span>Processing</span>
              </div>
            ) : isKycRejected ? (
              <div className="verified-badge-status rejected">
                <div className="rejected-dot"></div>
                <span>Rejected</span>
              </div>
            ) : (
              <button className="verify-now-btn">Verify Now</button>
            )}
          </div>

          {investments.length === 0 ? (
            <div className="empty-state">
              <div className="empty-icon">📊</div>
              <h2>No Record Found</h2>
              <p>You haven't purchased any plans yet.</p>
              <button className="active-plans-btn" onClick={() => setShowPlans(true)}>Explore Active Plans</button>
            </div>
          ) : (
            <>
              <div className="history-header">
                <h2>Investment History</h2>
                <button className="explore-btn" onClick={() => setShowPlans(true)}>
                  <span>+</span> Explore New Plans
                </button>
              </div>
              
              <div className="investment-history-grid">
                {investments.map((inv) => {
                  const pkgName = (inv.package_name || 'monthly').toLowerCase();
                  const months = pkgName.includes('12') ? 12 
                               : pkgName.includes('9') ? 9 
                               : pkgName.includes('6') ? 6 
                               : 1;
                  const durationLabel = months === 1 ? 'Monthly' : `${months} Month`;
                  const returnMoney = inv.amount * (inv.returns / 100) * months;

                  // Safe date parsing: fallback to inv.date if createdon_raw is missing or invalid
                  let createdDate = new Date();
                  if (inv.createdon_raw) {
                    createdDate = new Date(inv.createdon_raw);
                  } else if (inv.date) {
                    // Try to parse DD/MM/YYYY
                    const parts = inv.date.split('/');
                    if (parts.length === 3) {
                      createdDate = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
                    }
                  }

                  // If parsing still resulted in Invalid Date, fallback to current
                  if (isNaN(createdDate.getTime())) createdDate = new Date();

                  const maturityDate = new Date(createdDate);
                  maturityDate.setMonth(maturityDate.getMonth() + months);
                  
                  const now = new Date();
                  const isMatured = now >= maturityDate;
                  const isApproved = inv.status === 'Completed';
                  const wStatus = inv.w_status || 0;

                  // Better daysLeft logic
                  const diffTime = maturityDate.getTime() - now.getTime();
                  const daysLeft = Math.max(0, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));

                  return (
                  <div className="investment-card" key={inv.id}>
                    <div className="card-header-row">
                      <span className="card-date">{inv.date}</span>
                      <span className={`card-status-badge status-${inv.status.toLowerCase()}`}>
                        {inv.status}
                      </span>
                    </div>
                    <div className="card-main-content">
                      <div className="card-amount">
                        <span className="card-label">Investment</span>
                        <span className="card-value">{formatCurrency(inv.amount)}</span>
                      </div>
                      <div className="card-strategy">
                        <span className="card-label">Strategy</span>
                        <span className="card-value">{inv.strategy || 'Starter'}</span>
                      </div>
                      <div className="card-package">
                        <span className="card-label">Package</span>
                        <span className="card-value">{inv.package_name ? inv.package_name.charAt(0).toUpperCase() + inv.package_name.slice(1) : '-'}</span>
                      </div>
                      <div className="card-returns">
                        <span className="card-label">{durationLabel} Return</span>
                        <span className="card-value accent">{inv.returns}%</span>
                      </div>
                    </div>
                    <div className="card-profit-row">
                      <span className="profit-label">💰 {durationLabel} Profit</span>
                      <span className="profit-value">{formatCurrency(returnMoney)}</span>
                    </div>
                    <div className="card-total-row">
                      <span className="total-label">Total Payout</span>
                      <span className="total-value">{formatCurrency(Number(inv.amount) + returnMoney)}</span>
                    </div>
                    <div className="card-footer-row">
                      <div className="card-reward">
                        {inv.reward ? (
                          <span className="reward-badge">🎁 {inv.reward.replace(/🎁 |✈️ /, '')} Trip</span>
                        ) : (
                          <span className="no-reward">No Reward Included</span>
                        )}
                      </div>
                    </div>
                    {inv.status !== 'Rejected' && (
                      <div className="card-withdraw-row">
                        {wStatus === 0 && isMatured && isApproved ? (
                          <button className="withdraw-btn ready" onClick={() => handleWithdraw(inv)}>
                            💸 Withdraw Amount
                          </button>
                        ) : wStatus === 0 && (!isMatured || !isApproved) ? (
                          <button className="withdraw-btn locked" disabled title={!isApproved ? "Investment must be approved first" : ""}>
                            🔒 {isApproved ? `${daysLeft} days left to mature` : 'Waiting for Admin Approval'}
                          </button>
                        ) : wStatus === 1 ? (
                          <button className="withdraw-btn requested" disabled>
                            ⏳ Withdrawal Requested
                          </button>
                        ) : wStatus === 2 ? (
                          <button className="withdraw-btn approved" disabled>
                            ✅ Withdrawal Approved
                          </button>
                        ) : wStatus === 3 ? (
                          <button className="withdraw-btn rejected-w" onClick={() => handleWithdraw(inv)}>
                            ❌ Rejected — Request Again
                          </button>
                        ) : null}
                      </div>
                    )}
                  </div>
                  );
                })}
              </div>
            </>
          )}

          <div className="empty-rewards-section">
            <h3 className="section-title">Exclusive Rewards to Unlock</h3>
            {packages
              .filter((pkg) => pkg.trip)
              .map((pkg) => (
                <div key={pkg.id} className="reward-banner" onClick={() => { setSelectedPlan(pkg.key); setShowPlans(true); }}>
                  <div className="reward-icon">{pkg.trip.split(' ')[0]}</div>
                  <div className="reward-text">
                    <h3>{pkg.name.charAt(0).toUpperCase() + pkg.name.slice(1)} Reward</h3>
                    <p>{pkg.trip.replace(/🎁 |✈️ /, '')}</p>
                  </div>
                </div>
              ))}
          </div>
        </div>
      ) : (
        <div className="plans-view calculator-mode">
          <div className="plans-header">
            <button className="back-btn-simple" onClick={() => setShowPlans(false)}>← Back</button>
            <h2>Select a Package</h2>
          </div>
          
          <div className="calculator-container">
            <div className="calc-group">
              <label className="calc-label">Investment Amount</label>
              <div className="calc-select-wrapper">
                <select 
                  value={selectedAmount} 
                  onChange={(e) => setSelectedAmount(Number(e.target.value))}
                >
                  {plans.map(row => (
                    <option key={row.id} value={row.investment}>
                      ${row.investment.toLocaleString('en-IN')}
                    </option>
                  ))}
                </select>
                <div className="calc-select-arrow">▼</div>
              </div>
            </div>

            <div className="duration-grid">
              {packages.map((pkg) => (
                <button 
                  key={pkg.id} 
                  className={`duration-toggle ${selectedPlan === pkg.key ? 'active' : ''}`}
                  onClick={() => setSelectedPlan(pkg.key)}
                >
                  {pkg.name.replace(' plan', '').replace(' Plan', '')}
                </button>
              ))}
            </div>

            <div className="metrics-row">
              <div className="metric-item">
                <span className="metric-label">
                  {selectedPlan === 'monthly' ? 'Monthly' : 
                   selectedPlan === '6month' ? '6 Month' : 
                   selectedPlan === '9month' ? '9 Month' : 
                   selectedPlan === '12month' ? '12 Month' : 'Monthly'} Profit:
                </span>
                <span className="metric-value">{formatCurrency(getProfitValue())}</span>
              </div>
              <div className="metric-item">
                <span className="metric-label">
                  {selectedPlan === 'monthly' ? 'Monthly' : 
                   selectedPlan === '6month' ? '6 Month' : 
                   selectedPlan === '9month' ? '9 Month' : 
                   selectedPlan === '12month' ? '12 Month' : 'Monthly'} Return:
                </span>
                <span className="metric-value">{currentData.returns}%</span>
              </div>
            </div>

            {planDetails[selectedPlan].reward && (
              <div className="reward-display-small">
                <div className="reward-banner">
                  <div className="reward-icon">{planDetails[selectedPlan].reward.split(' ')[0]}</div>
                  <div className="reward-text">
                    <h3>Included Reward</h3>
                    <p>{planDetails[selectedPlan].reward.replace(/🎁 |✈️ /, '')}</p>
                  </div>
                </div>
              </div>
            )}

            <button className="pay-btn-large" onClick={handleProceed}>
              Proceed to Pay
            </button>
          </div>
        </div>
      )}

      {showWithdrawModal && (
        <div className="withdraw-modal-overlay">
          <div className="withdraw-modal">
            <h3>Enter Bank Details</h3>
            <p>Please enter the account details where you want to receive your funds.</p>
            
            <div className="withdraw-form">
              <div className="w-form-group">
                <label style={{textAlign: "left"}}>Bank Name</label>
                <input 
                  type="text" 
                  placeholder="e.g. State Bank of India"
                  value={bankData.bankName}
                  onChange={(e) => setBankData({...bankData, bankName: e.target.value})}
                />
              </div>
              <div className="w-form-group">
                <label style={{textAlign: "left"}}>Account Number</label>
                <input 
                  type="text" 
                  placeholder="Your Bank Account Number"
                  value={bankData.account}
                  onChange={(e) => setBankData({...bankData, account: e.target.value})}
                />
              </div>
              <div className="w-form-group">
                <label style={{textAlign: "left"}}>IFSC Code</label>
                <input 
                  type="text" 
                  placeholder="IFSC Code (e.g. SBIN0001234)"
                  value={bankData.ifsc}
                  onChange={(e) => setBankData({...bankData, ifsc: e.target.value})}
                />
              </div>
              <div className="w-checkbox-group">
                <input 
                  type="checkbox" 
                  id="saveProfile"
                  checked={bankData.saveToProfile}
                  onChange={(e) => setBankData({...bankData, saveToProfile: e.target.checked})}
                />
                <label htmlFor="saveProfile">Save to profile for future payouts</label>
              </div>

              <div className="w-checkbox-group">
                <input 
                  type="checkbox" 
                  id="agreeFee"
                  checked={bankData.agreedToFee}
                  onChange={(e) => setBankData({...bankData, agreedToFee: e.target.checked})}
                />
                <label htmlFor="agreeFee">I agree to the 2% withdrawal fee</label>
              </div>

              <div className="w-checkbox-group">
                <input 
                  type="checkbox" 
                  id="agreeTime"
                  checked={bankData.agreedToTime}
                  onChange={(e) => setBankData({...bankData, agreedToTime: e.target.checked})}
                />
                <label htmlFor="agreeTime">I understand the processing time is 24-48 hours</label>
              </div>

              {selectedInv && (
                <div className="withdraw-summary-box">
                  <h4>Withdrawal Summary</h4>
                  <div className="summary-details">
                    <div className="summary-row">
                      <span>Principal Investment:</span>
                      <span>{formatCurrency(selectedInv.amount)}</span>
                    </div>
                    <div className="summary-row">
                      <span>Total Profits:</span>
                      <span>{formatCurrency(selectedInv.amount * (selectedInv.returns / 100) * (
                        (selectedInv.package_name || '').toLowerCase().includes('12') ? 12 
                        : (selectedInv.package_name || '').toLowerCase().includes('9') ? 9 
                        : (selectedInv.package_name || '').toLowerCase().includes('6') ? 6 
                        : 1
                      ))}</span>
                    </div>
                    <div className="summary-row fee">
                      <span>Withdrawal Fee (2%):</span>
                      <span>- {formatCurrency(
                        (Number(selectedInv.amount) + (selectedInv.amount * (selectedInv.returns / 100) * (
                          (selectedInv.package_name || '').toLowerCase().includes('12') ? 12 
                          : (selectedInv.package_name || '').toLowerCase().includes('9') ? 9 
                          : (selectedInv.package_name || '').toLowerCase().includes('6') ? 6 
                          : 1
                        ))) * 0.02
                      )}</span>
                    </div>
                    <div className="summary-divider"></div>
                    <div className="summary-row payout">
                      <span>You will receive:</span>
                      <span>{formatCurrency(
                        (Number(selectedInv.amount) + (selectedInv.amount * (selectedInv.returns / 100) * (
                          (selectedInv.package_name || '').toLowerCase().includes('12') ? 12 
                          : (selectedInv.package_name || '').toLowerCase().includes('9') ? 9 
                          : (selectedInv.package_name || '').toLowerCase().includes('6') ? 6 
                          : 1
                        ))) * 0.98
                      )}</span>
                    </div>
                  </div>
                </div>
              )}

              <div className="withdraw-modal-actions">
                <button className="w-cancel-btn" onClick={() => setShowWithdrawModal(false)}>Cancel</button>
                <button 
                  className={`w-confirm-btn ${isSubmittingWithdraw || !bankData.agreedToFee || !bankData.agreedToTime ? 'loading' : ''}`} 
                  onClick={confirmWithdrawal} 
                  disabled={isSubmittingWithdraw || !bankData.agreedToFee || !bankData.agreedToTime}
                >
                  {isSubmittingWithdraw ? 'Processing...' : 'Submit Payout Request'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </main>
  );
}
