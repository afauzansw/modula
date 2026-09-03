import { Form, Link } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/instructor/courses';
import type { CategoryOption, InstructorCourseFormValues } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

const control =
    'flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50';

type Props = {
    categories: CategoryOption[];
    /** Omit to create; pass the row to edit. */
    course?: InstructorCourseFormValues;
    form: RouteFormDefinition<'post'>;
    submitLabel: string;
};

/** The shared course create / edit form (a full page, not a modal). */
export function CourseForm({ categories, course, form, submitLabel }: Props) {
    const [isFree, setIsFree] = useState(course?.is_free ?? true);

    return (
        <Form
            {...form}
            options={{ preserveScroll: true }}
            className="max-w-2xl space-y-6"
        >
            {({ errors, processing }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="title">Title</Label>
                        <Input
                            id="title"
                            name="title"
                            required
                            autoFocus
                            defaultValue={course?.title}
                        />
                        <InputError message={errors.title} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="category_id">Category</Label>
                        <select
                            id="category_id"
                            name="category_id"
                            className={control}
                            defaultValue={course?.category_id ?? ''}
                        >
                            <option value="">Uncategorized</option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.category_id} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            rows={4}
                            className={control}
                            defaultValue={course?.description ?? ''}
                        />
                        <InputError message={errors.description} />
                    </div>

                    <div className="flex items-center gap-2">
                        <Checkbox
                            id="is_free"
                            name="is_free"
                            value="1"
                            checked={isFree}
                            onCheckedChange={(value) =>
                                setIsFree(value === true)
                            }
                        />
                        <Label htmlFor="is_free" className="font-normal">
                            Free course
                        </Label>
                    </div>

                    {!isFree && (
                        <div className="grid gap-2">
                            <Label htmlFor="price">Price (IDR)</Label>
                            <Input
                                id="price"
                                name="price"
                                type="number"
                                min={0}
                                step={1000}
                                defaultValue={course?.price || ''}
                            />
                            <InputError message={errors.price} />
                        </div>
                    )}

                    <div className="grid gap-2">
                        <Label htmlFor="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            className={control}
                            defaultValue={course?.status ?? 'draft'}
                        >
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                            <option value="archived">Archived</option>
                        </select>
                        <InputError message={errors.status} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="thumbnail">Thumbnail</Label>
                        {course?.thumbnail && (
                            <img
                                src={course.thumbnail}
                                alt=""
                                className="aspect-video w-48 rounded object-cover"
                            />
                        )}
                        <Input
                            id="thumbnail"
                            name="thumbnail"
                            type="file"
                            accept="image/*"
                        />
                        <InputError message={errors.thumbnail} />
                    </div>

                    <div className="flex gap-2">
                        <Button type="submit" disabled={processing}>
                            {submitLabel}
                        </Button>
                        <Button variant="secondary" asChild>
                            <Link href={index()}>Cancel</Link>
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
