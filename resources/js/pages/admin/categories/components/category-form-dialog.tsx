import { Form } from '@inertiajs/react';
import { useState } from 'react';
import CategoryController from '@/actions/App/Http/Controllers/Admin/CategoryController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { CategoryListItem } from '@/types';

type Props = {
    /** Element that opens the dialog (rendered via `DialogTrigger asChild`). */
    trigger: React.ReactNode;
    /** Omit to create; pass a table row to edit it. */
    category?: CategoryListItem;
};

/**
 * Create / edit a course category in a modal. `category` absent → `store`,
 * present → `update`. The slug is derived from the name server-side.
 */
export function CategoryFormDialog({ trigger, category }: Props) {
    const [open, setOpen] = useState(false);

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogTitle>
                    {category ? `Edit ${category.name}` : 'New category'}
                </DialogTitle>
                <DialogDescription>
                    {category
                        ? 'Rename this category — its slug follows the name.'
                        : 'Add a category courses can be organized under. The slug is generated from the name.'}
                </DialogDescription>

                <Form
                    {...(category
                        ? CategoryController.update.form(category.id)
                        : CategoryController.store.form())}
                    options={{ preserveScroll: true }}
                    onSuccess={() => setOpen(false)}
                    className="space-y-6"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    autoFocus
                                    defaultValue={category?.name}
                                    placeholder="e.g. Web Development"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="secondary" type="button">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={processing}>
                                    {category ? 'Save' : 'Create category'}
                                </Button>
                            </DialogFooter>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
