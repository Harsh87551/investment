import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import Signup from './components/Signup/Signup'
import Login from './components/Login/Login'
import Dashboard from './components/Dashboard/Dashboard'
import Kyc from './components/Kyc/Kyc'
import Payment from './components/Payment/Payment'
import Layout from './components/Layout/Layout'
import './App.css'

function App() {
  return (
    <Router>
      {/* Investment/Stock Market Animated Background */}
      <div className="investment-bg">
        <div className="chart-bar up-trend bar-1"></div>
        <div className="chart-bar up-trend bar-2"></div>
        <div className="chart-bar down-trend bar-3"></div>
        <div className="chart-bar up-trend bar-4"></div>
        <div className="chart-bar down-trend bar-5"></div>
        <div className="chart-bar up-trend bar-6"></div>
        <div className="trend-line"></div>
      </div>

      <div style={{ flexGrow: 1, display: 'flex', flexDirection: 'column', position: 'relative', zIndex: 1 }}>
        <Routes>
          <Route path="/" element={<Navigate to="/signup" replace />} />
          <Route path="/signup" element={<Signup />} />
          <Route path="/login" element={<Login />} />
          
          <Route element={<Layout key={localStorage.getItem('token') || 'no-auth'} />}>
            <Route path="/dashboard" element={<Dashboard />} />
            <Route path="/kyc" element={<Kyc />} />
            <Route path="/payment" element={<Payment />} />
          </Route>
        </Routes>
      </div>
    </Router>
  )
}

export default App
