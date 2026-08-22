import { AppProviders } from './app/providers';
import { AppRoutes } from './routes/AppRoutes';
import { ToastContainer } from './features/toast/ToastContainer';

function App() {
  return (
    <AppProviders>
      <AppRoutes />
      <ToastContainer />
    </AppProviders>
  );
}

export default App;
