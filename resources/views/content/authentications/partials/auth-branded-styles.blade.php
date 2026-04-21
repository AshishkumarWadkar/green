<style>
  :root {
    --primary-green: #28c76f;
    --hover-green: #21a35c;
    --dark-text: #2f2b3d;
    --muted-text: #6f6b7d;
  }

  .auth-page-wrapper {
    display: flex;
    min-height: 100vh;
    width: 100%;
    background-color: #f8f7fa;
    position: relative;
    overflow: hidden;
  }

  .auth-side-content {
    display: none;
    flex: 1;
    background: url('/assets/img/branding/login-bg.png') no-repeat center center;
    background-size: cover;
    position: relative;
  }

  .auth-side-content::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.45) 0%, rgba(15, 20, 18, 0.75) 100%);
  }

  .auth-side-content .brand-overlay {
    position: absolute;
    bottom: 80px;
    left: 80px;
    z-index: 2;
    color: #fff;
    max-width: 450px;
  }

  .brand-overlay h2 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
    color: #fff;
  }

  .brand-overlay p {
    font-size: 1.1rem;
    line-height: 1.6;
    opacity: 0.9;
  }

  .auth-form-side {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    background: #fff;
    padding: 3rem;
  }

  @media (min-width: 992px) {
    .auth-side-content {
      display: flex;
    }
    .auth-form-side {
      width: 450px;
      min-width: 450px;
    }
  }

  @media (min-width: 1200px) {
    .auth-form-side {
      width: 500px;
      min-width: 500px;
    }
  }

  .app-brand-logo img {
    width: 50px;
    height: 50px;
    object-fit: contain;
  }

  .auth-header h3 {
    font-weight: 700;
    color: var(--dark-text);
    margin-bottom: 0.5rem;
  }

  .auth-header p {
    color: var(--muted-text);
  }

  .form-label {
    font-size: 0.8125rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--dark-text);
    margin-bottom: 0.5rem;
  }

  .form-control {
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    border-radius: 0.5rem;
    border: 1px solid #dbdade;
  }

  .form-control:focus {
    border-color: var(--primary-green);
    box-shadow: 0 0 0 0.15rem rgba(40, 199, 111, 0.12);
  }

  .btn-login {
    background-color: var(--primary-green) !important;
    border-color: var(--primary-green) !important;
    padding: 0.75rem;
    font-weight: 700;
    border-radius: 0.5rem;
    transition: all 0.2s ease-in-out;
  }

  .btn-login:hover {
    background-color: var(--hover-green) !important;
    border-color: var(--hover-green) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(40, 199, 111, 0.3);
  }

  .text-primary {
    color: var(--primary-green) !important;
    font-weight: 600;
  }

  .form-check-input:checked {
    background-color: var(--primary-green);
    border-color: var(--primary-green);
  }

  .input-group-text {
    border-color: #dbdade;
    background-color: #f8f7fa;
  }
</style>
