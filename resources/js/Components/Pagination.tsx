import { Link } from '@inertiajs/react';

export interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface PaginationProps {
  links?: PaginationLink[];
}

function formatPaginationLabel(raw: string): string {
  return raw
    .replace(/&laquo;/g, '«')
    .replace(/&raquo;/g, '»')
    .replace(/&amp;/g, '&');
}

export default function Pagination({ links }: PaginationProps) {
  if (!links || links.length <= 3) {
    return null;
  }

  return (
    <div className="card-footer bg-white py-3 border-top">
      <nav aria-label="Pagination" className="d-flex justify-content-center">
        <ul className="pagination pagination-sm mb-0">
          {links.map((link, idx) => {
            const label = formatPaginationLabel(link.label);
            return (
              <li
                key={idx}
                className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}
              >
                {link.url ? (
                  <Link href={link.url} className="page-link" preserveScroll>
                    {label}
                  </Link>
                ) : (
                  <span className="page-link">{label}</span>
                )}
              </li>
            );
          })}
        </ul>
      </nav>
    </div>
  );
}

