type Props = {
    columns: string[];
};

export function MockupTable({ columns }: Props) {
    return (
        <div className="overflow-x-auto rounded-lg border">
            <table className="w-full text-sm">
                <thead className="border-b bg-muted/50 text-left">
                    <tr>
                        {columns.map((column) => (
                            <th key={column} className="px-4 py-2 font-medium">
                                {column}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td
                            colSpan={columns.length}
                            className="px-4 py-6 text-center text-muted-foreground"
                        >
                            Mockup — no data wired up yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    );
}
