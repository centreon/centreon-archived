import { lazy, Suspense, useEffect, useMemo, useState } from 'react';

import { atom, useAtom } from 'jotai';

import { MenuSkeleton, PageSkeleton } from '@centreon/ui';

import NotFoundPage from '../../FallbackPages/NotFoundPage';

import loadComponent from './loadComponent';

interface UseDynamicLoadRemoteEntryState {
  failed: boolean;
  ready: boolean;
}

interface UseDynamicLoadRemoteEntryProps {
  moduleName: string;
  remoteEntry: string;
}

const remoteEntriesLoadedAtom = atom([] as Array<string>);

const useDynamicLoadRemoteEntry = ({
  remoteEntry,
  moduleName,
}: UseDynamicLoadRemoteEntryProps): UseDynamicLoadRemoteEntryState => {
  const [failed, setFailed] = useState(false);

  const [remoteEntriesLoaded, setRemoteEntriesLoaded] = useAtom(
    remoteEntriesLoadedAtom,
  );

  useEffect((): (() => void) | undefined => {
    if (!remoteEntry) {
      return undefined;
    }

    const remoteEntryScript = document.getElementById(moduleName);

    if (remoteEntryScript || remoteEntriesLoaded.includes(moduleName)) {
      return undefined;
    }

    const element = document.createElement('script');
    element.src = `./modules/${moduleName}/static/${remoteEntry}`;
    element.type = 'text/javascript';
    element.id = moduleName;
    element.async = true;

    element.onload = (): void => {
      setRemoteEntriesLoaded([...remoteEntriesLoaded, moduleName]);
    };

    element.onerror = (): void => {
      setFailed(true);
    };

    document.head.appendChild(element);

    return () => {
      document.head.removeChild(element);
    };
  }, [remoteEntriesLoadedAtom]);

  return {
    failed,
    ready: remoteEntriesLoaded.includes(moduleName),
  };
};

interface LoadComponentProps {
  component: string;
  isHook?: boolean;
  name: string;
}

const LoadComponent = ({
  name,
  component,
  isHook,
  ...props
}: LoadComponentProps): JSX.Element => {
  const Component = useMemo(
    () => lazy(loadComponent({ component, moduleName: name })),
    [name],
  );

  return (
    <Suspense
      fallback={isHook ? <MenuSkeleton width={29} /> : <PageSkeleton />}
    >
      <Component {...props} />
    </Suspense>
  );
};

interface RemoteProps extends LoadComponentProps {
  moduleName: string;
  remoteEntry: string;
}

export const Remote = ({
  component,
  remoteEntry,
  moduleName,
  name,
  isHook,
  ...props
}: RemoteProps): JSX.Element => {
  const { ready, failed } = useDynamicLoadRemoteEntry({
    moduleName,
    remoteEntry,
  });

  if (!ready) {
    return isHook ? <MenuSkeleton width={29} /> : <PageSkeleton />;
  }

  if (failed) {
    return <NotFoundPage />;
  }

  return (
    <LoadComponent
      component={component}
      isHook={isHook}
      name={name}
      {...props}
    />
  );
};
