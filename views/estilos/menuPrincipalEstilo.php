<style>
    /* 🌟 Tarjetas con efecto hover elegante */
    .small-box {
      border-radius: 1rem;
      transition: all 0.25s ease;
      transform: translateY(0);
    }

    .small-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      filter: brightness(1.05);
    }

    .small-box svg {
      transition: transform 0.3s ease;
    }

    .small-box:hover svg {
      transform: scale(1.1);
    }

    @media (max-width: 768px) {
      .small-box {
        margin-bottom: 1rem;
      }
    }
</style>
