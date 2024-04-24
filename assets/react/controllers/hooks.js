import { useState, useEffect } from 'react'

export function useFetch(data, form) {
    const [fetchedData, setFetchedData] = useState(null)
    const [isLoading, setLoading] = useState(true)
    const [error, setError] = useState(false)

    useEffect(() => {
        if (!data) return
        setLoading(true)
        async function fetchData() {
            try {
                const response = await fetch(form.action, {
                                            method: 'post',
                                            body: data,
                                            headers: {
                                            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                                            }
                                        })
                const fetchedData = await response.text()
                setFetchedData(fetchedData)
            } catch (err) {
                console.log(err)
                setError(true)
            } finally {
                setLoading(false)
            }
        }
        fetchData()
    }, [data])
    return { isLoading, fetchedData, error }
}
