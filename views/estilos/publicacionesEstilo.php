<style>
  :root{
    --ev-green:#0F592F;
    --ev-green-2:#115C41;
    --ev-cream:#FFF9F0;
    --ev-gray:#F1F3F5;
    --ev-text:#1F2937;
    --ev-danger:#DE3B3B;
  }

  .ev-pub-header{
    background: var(--ev-green-2);
    border: 0;
    padding: .9rem 1.25rem;
  }

  .btn-ev-primary{
    background: var(--ev-green);
    color:#fff;
    border: 0;
    padding: .45rem .9rem;
    border-radius: .75rem;
  }
  .btn-ev-primary:hover{ filter: brightness(1.05); }

  .btn-ev-outline{
    background: transparent;
    border: 1.5px solid rgba(255,255,255,.7);
    color: #fff;
    padding: .45rem .9rem;
    border-radius: .75rem;
  }
  .btn-ev-outline:hover{ background: rgba(255,255,255,.1); }

  .btn-ev-danger{
    background: var(--ev-danger);
    color:#fff;
    border:0;
    border-radius:.6rem;
    padding:.35rem .7rem;
  }
  .btn-ev-danger:hover{ filter:brightness(1.05); }

  .btn-ev-ghost{
    background: transparent;
    border: 1px solid #E5E7EB;
    color: #334155;
  }
  .btn-ev-ghost:hover{ background: #F8FAFC; }

  .ev-pub-table thead th{
    background: var(--ev-gray);
    color: #374151;
    font-weight: 600;
    border-top: 0;
    border-bottom: 1px solid #e5e7eb;
  }
  .ev-pub-table tbody td{
    color: var(--ev-text);
    vertical-align: middle;
  }
  .ev-pub-table tbody tr:hover{
    background: #FAFFFB;
  }

  .ev-badge{
    display: inline-block;
    background: #E6F2EB;
    color: var(--ev-green);
    padding: .25rem .5rem;
    border-radius: .5rem;
    font-weight: 600;
    font-size: .85rem;
  }
  .ev-code{
    font-variant-numeric: tabular-nums;
    letter-spacing: .5px;
    color: #475569;
  }
  .ev-chip{
    display: inline-block;
    background: var(--ev-cream);
    color: var(--ev-green-2);
    border: 1px solid #FFE8D1;
    padding: .2rem .5rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: .8rem;
  }

  .ev-select{
    width: 80px;
    border-radius: .6rem;
  }

  .ev-publist .card{ overflow: hidden; }

  /* Responsive tabla → usa data-label en <td> */
  @media (max-width: 576px){
    .ev-pub-table thead { display: none; }
    .ev-pub-table tbody tr{
      display: grid;
      grid-template-columns: 1fr;
      gap: .25rem;
      border-bottom: 1px solid #f1f5f9;
      padding: .6rem .75rem;
    }
    .ev-pub-table tbody td{
      display: flex;
      justify-content: space-between;
      gap: .75rem;
    }
    .ev-pub-table tbody td::before{
      content: attr(data-label);
      font-weight: 600;
      color: #64748b;
    }
  }
</style>
