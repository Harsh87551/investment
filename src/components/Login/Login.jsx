import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import '../Signup/Signup.css';
import { loginUser } from '../../services/authService';

export default function Login() {

  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {

    e.preventDefault();

    try {

      const res = await loginUser(formData);

      console.log(res.data);

      if (res.data.token) {

        // Clear old session data first
        localStorage.removeItem('registeredUser');
        localStorage.removeItem('userProfilePic');

        // Store new session data
        localStorage.setItem("token", res.data.token);
        localStorage.setItem("isLoggedIn", "true");

        // Store user data from backend response
        if (res.data.user) {
          localStorage.setItem("registeredUser", JSON.stringify(res.data.user));
        }

        navigate("/dashboard");

      } else {

        alert(res.data.message);

      }

    } catch (error) {

      console.log(error);
      alert("Login failed");

    }

  };

  return (
    <div className="auth-container">
      <div className="hero-heading">
        <h1>Investment Plans</h1>
        <p>Unlock your financial future today.</p>
      </div>

      <h2>Welcome Back</h2>

      <form onSubmit={handleSubmit} className="auth-form">

        <div className="form-group">
          <label>Email</label>

          <input
            type="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
            placeholder="Enter your email"
          />

        </div>

        <div className="form-group">
          <label>Password</label>

          <input
            type="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            required
            placeholder="Enter your password"
          />

        </div>

        <button type="submit" className="auth-button">
          Log In
        </button>

      </form>

      <p className="auth-link">
        Don't have an account? <Link to="/signup">Sign up</Link>
      </p>

    </div>
  );
}