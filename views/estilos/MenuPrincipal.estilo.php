<!-- Estilos UX/UI personalizados -->
  <style>
    .small-box {
      border-radius: 0.75rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.2s ease-in-out, box-shadow 0.2s;
      min-height: 200px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 1rem;
    }

    .small-box:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }

    .small-box-icon {
      width: 48px;
      height: 48px;
      margin-top: 1rem;
      color: #ffffff80;
    }

    .small-box-footer {
      font-weight: 500;
      font-size: 0.95rem;
      padding-top: 0.5rem;
      display: inline-block;
      transition: opacity 0.2s ease-in-out;
    }

    .small-box-footer:hover {
      opacity: 0.85;
    }

    .callout-primary {
      background-color: #22c55e1a;
      border-left: 5px solid #22c55e;
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 1rem;
    }

    .callout-primary h5 {
      color: #198754;
      font-weight: 600;
      margin: 0;
    }

    .breadcrumb a {
      color: #198754;
      text-decoration: none;
    }

    .breadcrumb a:hover {
      text-decoration: underline;
    }

    @media (max-width: 767.98px) {
      .small-box {
        margin-bottom: 1rem;
      }
    }
  </style>