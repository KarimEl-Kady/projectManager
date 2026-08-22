export function Spinner() {
  return <span className="spinner" role="status" aria-label="Loading" />;
}

export function PageLoader() {
  return (
    <div className="page-loader">
      <Spinner />
    </div>
  );
}
