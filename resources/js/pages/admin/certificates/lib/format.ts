const issued = new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' });

/** An ISO timestamp as `12 Aug 2026`. */
export function formatIssuedAt(iso: string): string {
    return issued.format(new Date(iso));
}
