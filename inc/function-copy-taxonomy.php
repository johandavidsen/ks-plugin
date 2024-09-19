<?php
/**
 * This file contains a function to copy a taxonomy from one site to another.
 *
 * @namespace KSPlugin
 */

namespace KSPlugin;

if (! defined('ABSPATH')) {
    exit;
}

use WP_Term;

/**
 * Copy a taxonomy from one site to another.
 *
 * @param string $taxonomy
 * @param int $from
 * @param int $to
 *
 * @return void
 */
function ks_copy_taxonomy(string $taxonomy, int $from, int $to): void
{
    // Switch to the source site
    \switch_to_blog($from);

    $terms = \get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'parent'     => 0,
    ]);

    $from_departments = [];

    // Insert the departments into the destination site
    foreach ($terms as $term) {
        $from_departments = copy_term_recursive($taxonomy, $term, $from_departments);
    }

    // Switch to the destination site
    \switch_to_blog($to);

	// Clear the department taxonomy
	remove_terms($taxonomy);

    //
    $translations_mappings = [];

    /**
     * Insert the terms into the destination site.
     */
    foreach ($from_departments as $from_department) {
        $args = [
            'description' => $from_department['description'],
            'slug'        => $from_department['slug'],
        ];

        // Get the ID of the parent term
        if (! is_null($from_department['parent_name'])) {
            $parent         = \get_term_by('slug', $from_department['parent_name'], $taxonomy);
            $args['parent'] = $parent->term_id;
        }

        // Insert the term
        $term = \wp_insert_term(
            $from_department['name'],
            $taxonomy,
            $args
        );

        // Set the language of the term
        \pll_set_term_language($term['term_id'], $from_department['language_term']);

        // Set the ACF fields
        if (!empty($from_department['meta'])) {
            foreach ($from_department['meta'] as $key => $value) {
                \update_term_meta($term['term_id'], $key, $value);
            }
        }

        // Set the translations of the terms
        if (function_exists('pll_get_term_translations')) {
            $translations_mappings[$term['term_id']] = $from_department['language_translation'];
        }
    }

    /**
     * Set the translations of the terms
     */
    foreach ($translations_mappings as $key => $translations) {
        // Make sure that the translations are available
        if (array_key_exists('da', $translations) && array_key_exists('kl', $translations)) {
            $danish_term_name = $translations['da'];
            $greenlandic_term_name = $translations['kl'];

            $danish_term = get_term_by('slug', $danish_term_name, $taxonomy);
            $greenlandic_term = get_term_by('slug', $greenlandic_term_name, $taxonomy);

            if (!is_bool($danish_term) && !is_bool($greenlandic_term)) {
                // Set the translations
                pll_save_term_translations([
                    'da' => $danish_term->term_id,
                    'kl' => $greenlandic_term->term_id
                ]);
            }
        }
    }

    // Restore the current blog
    \restore_current_blog();
}

/**
 * This function removes all terms from a taxonomy.
 *
 * @param string $taxonomy
 *
 * @return void
 */
function remove_terms(string $taxonomy): void
{
	$terms = \get_terms([
		'taxonomy' => $taxonomy,
		'hide_empty' => false,
	]);

	foreach ($terms as $term) {
		\wp_delete_term($term->term_id, $taxonomy);
	}
}

/**
 * Recursively copy a term and its children, including their languages.
 *
 * @param string $taxonomy
 * @param WP_Term $term
 * @param array $result
 *
 * @return array
 */
function copy_term_recursive(string $taxonomy, WP_Term $term, array $result): array
{
    /**
     *
     */
    $new_term_array = [
        'name'        => $term->name,
        'description' => $term->description,
        'slug'        => $term->slug,
        'parent_name' => null
    ];

    if (function_exists('pll_get_term_translations')) {
        $new_term_array['language_term'] = pll_get_term_language($term->term_id);
    }

    if (function_exists('pll_get_term_translations')) {
        // Convert this to an array and get the name of the terms
        $new_term_array['language_translation'] = collect(
            pll_get_term_translations($term->term_id)
        )->map(function ($value, $key) {
            return get_term($value)->slug;
        })->toArray();
    }

    // Copy ACF fields
    $new_term_array['meta'] = copy_acf_fields($taxonomy . '_' . $term->term_id);

    if ($term->parent > 0) {
        $new_term_array['parent_name'] = get_term($term->parent)->slug;
    }

    $result[] = $new_term_array;

    // Retrieve children of the current term
    $children = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'parent'     => $term->term_id,
    ]);

    // Copy each child term recursively
    foreach ($children as $child) {
        $result = copy_term_recursive($taxonomy, $child, $result);
    }

    return $result;
}

/**
 * Copy ACF fields from one term to another.
 *
 * @param string $from_term_id
 *
 * @return array|false
 */
function copy_acf_fields(string $from_term_id): bool|array
{
    if (!function_exists('get_fields')) {
        return [];
    }
    return get_fields($from_term_id);
}

ks_copy_taxonomy('department', 1, 4);
