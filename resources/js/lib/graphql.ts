type GraphQLErrorItem = {
    message: string;
    [key: string]: unknown;
};

type GraphQLResult<TData> = {
    data?: TData;
    errors?: GraphQLErrorItem[];
};

function getXsrfTokenFromCookie(): string | null {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : null;
}

export async function requestWrapper<TData, TVariables = Record<string, unknown>>(
    query: string,
    variables?: TVariables,
): Promise<TData> {
    const xsrfToken = getXsrfTokenFromCookie();

    let response: Response;

    try {
        response = await fetch('/graphql', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ query, variables }),
        });
    } catch {
        throw new Error('Network error. Please check your connection and try again.');
    }

    if (!response.ok) {
        throw new Error(`Server error (${response.status}). Please try again later.`);
    }

    let result: GraphQLResult<TData>;

    try {
        result = await response.json();
    } catch {
        throw new Error('Invalid server response. Please try again later.');
    }

    if (result.errors?.length) {
        const message = result.errors[0]?.message ?? 'Error executing GraphQL operation.';

        const error = new Error(message);
        (error as any).graphQLErrors = result.errors;

        throw error;
    }

    if (!result.data) {
        throw new Error('Invalid GraphQL response: missing "data" field.');
    }

    return result.data;
}
