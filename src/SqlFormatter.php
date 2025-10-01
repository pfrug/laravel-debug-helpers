<?php

namespace Pfrug\LaravelDebugHelpers;


class SqlFormatter
{
    /**
     * Formats SQL query with proper indentation and structure
     */
    public static function format(string $sql): string
    {
        $sql = self::normalizeSqlSpacing($sql);
        $lines = self::splitSqlIntoLines($sql);
        $lines = self::expandLogicalGroups($lines);
        $lines = self::indentSqlLines($lines);
        return self::uppercaseKeywords($lines);
    }

    /**
     * Normalizes whitespace in SQL string
     */
    private static function normalizeSqlSpacing(string $sql): string
    {
        return preg_replace('/\s+/', ' ', trim($sql));
    }

    /**
     * Splits SQL into lines based on keywords (SELECT, FROM, WHERE, etc.)
     */
    private static function splitSqlIntoLines(string $sql): array
    {
        $sql = preg_replace_callback('/\b(LEFT OUTER JOIN|RIGHT OUTER JOIN|FULL OUTER JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN|OUTER JOIN|JOIN)\b/i', fn($m) => "\n    " . strtoupper($m[1]), $sql);
        $sql = preg_replace_callback('/\b(SELECT|FROM|WHERE|ORDER BY|GROUP BY|HAVING|LIMIT|OFFSET)\b/i', fn($m) => "\n" . strtoupper($m[1]), $sql);
        $sql = preg_replace_callback('/\b(AND|OR)\b/i', fn($m) => "\n" . strtoupper($m[1]), $sql);

        return array_filter(array_map('trim', explode("\n", $sql)));
    }

    /**
     * Expands logical groups with parentheses into multiple lines when they contain AND/OR
     */
    private static function expandLogicalGroups(array $lines): array
    {
        $expanded = [];
        $i = 0;

        while ($i < count($lines)) {
            if (!isset($lines[$i])) {
                $i++;
                continue;
            }

            $line = trim($lines[$i]);
            if (empty($line)) {
                $i++;
                continue;
            }

            // Look for lines that start with "AND (" or "OR ("
            if (preg_match('/^(AND|OR)\s+\(/', $line)) {
                // Collect the full logical group (might span multiple lines)
                $fullGroup = $line;
                $openParens = substr_count($line, '(');
                $closeParens = substr_count($line, ')');
                $i++;

                // Keep adding lines until we close all parentheses
                while ($openParens > $closeParens && $i < count($lines)) {
                    if (isset($lines[$i])) {
                        $fullGroup .= ' ' . trim($lines[$i]);
                        $openParens += substr_count($lines[$i], '(');
                        $closeParens += substr_count($lines[$i], ')');
                    }
                    $i++;
                }

                // Now process the complete group
                if (preg_match('/^(AND|OR)\s+\((.+)\)$/', $fullGroup, $matches)) {
                    $operator = $matches[1];
                    $content = trim($matches[2]);

                    // Check if content contains AND/OR (indicating multiple conditions)
                    if (preg_match('/\b(AND|OR)\b/', $content)) {
                        $expanded[] = $operator . ' (';

                        // Split content by AND/OR while preserving the operators
                        $parts = preg_split('/\s+(AND|OR)\s+/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

                        foreach ($parts as $part) {
                            $part = trim($part);
                            if (!empty($part)) {
                                $expanded[] = $part;
                            }
                        }

                        $expanded[] = ')';
                    } else {
                        // Single condition, keep as is
                        $expanded[] = $fullGroup;
                    }
                } else {
                    $expanded[] = $fullGroup;
                }
            } else {
                $expanded[] = $line;
                $i++;
            }
        }

        return $expanded;
    }

    /**
     * Applies proper indentation to SQL lines based on their type and nesting level
     */
    private static function indentSqlLines(array $lines): array
    {
        $formatted = [];
        $insideParentheses = false;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) continue;

            $upperLine = strtoupper($trimmedLine);

            // SELECT with column separation
            if (str_starts_with($upperLine, 'SELECT')) {
                $formatted[] = 'SELECT';
                $columns = explode(',', substr($trimmedLine, 6));
                foreach ($columns as $i => $col) {
                    $col = trim($col);
                    if ($i === count($columns) - 1) {
                        $formatted[] = '    ' . $col;
                    } else {
                        $formatted[] = '    ' . $col . ',';
                    }
                }
                continue;
            }

            // Main SQL keywords
            if (preg_match('/^(FROM|WHERE|GROUP BY|ORDER BY|HAVING|LIMIT|OFFSET)(\s|$)/i', $trimmedLine)) {
                $formatted[] = $trimmedLine;
                continue;
            }

            // JOINs
            if (preg_match('/^(LEFT|RIGHT|FULL|INNER|OUTER)?\s*JOIN/i', $trimmedLine)) {
                $formatted[] = '    ' . $trimmedLine;
                continue;
            }

            // Opening parenthesis from logical groups
            if (preg_match('/^(AND|OR)\s+\($/', $trimmedLine)) {
                $formatted[] = '    ' . $trimmedLine;
                $insideParentheses = true;
                continue;
            }

            // Closing parenthesis
            if ($trimmedLine === ')') {
                $formatted[] = '    )';
                $insideParentheses = false;
                continue;
            }

            // AND/OR inside parentheses get extra indentation
            if ($insideParentheses && preg_match('/^(AND|OR)(\s|$)/i', $trimmedLine)) {
                $formatted[] = '        ' . $trimmedLine;
                continue;
            }

            // Conditions inside parentheses get extra indentation
            if ($insideParentheses) {
                $formatted[] = '        ' . $trimmedLine;
                continue;
            }

            // Regular AND/OR and conditions
            $formatted[] = '    ' . $trimmedLine;
        }

        return $formatted;
    }



    /**
     * Converts SQL keywords to uppercase for better readability
     */
    private static function uppercaseKeywords(array $lines): string
    {
        $final = implode("\n", $lines);

        $keywords = [
            'as', 'on', 'is', 'null', 'not', 'in',
            'like', 'between', 'exists', 'case',
            'when', 'then', 'else', 'end'
        ];

        return preg_replace_callback('/\b(' . implode('|', $keywords) . ')\b/i', fn($m) => strtoupper($m[1]), $final);
    }
}
