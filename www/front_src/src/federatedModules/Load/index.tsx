import { lazy, Suspense, useEffect, useMemo, useState } from 'react';

import { atom, useAtom } from 'jotai';
import { isEmpty } from 'ramda';

import { MenuSkeleton, PageSkeleton, useMemoComponent } from '@centreon/ui';

import NotFoundPage from '../../FallbackPages/NotFoundPage';

import loadComponent from './loadComponent';

interface UseDynamicLoadRemoteEntryState {
  failed: boolean;
  ready: boolean;
}

interface UseDynamicLoadRemoteEntryProps {
  isFederatedWidget?: boolean;
  moduleName: string;
  remoteEntry: string;
}

const remoteEntriesLoadedAtom = atom([] as Array<string>);

const useDynamicLoadRemoteEntry = ({
  remoteEntry,
  moduleName,
  isFederatedWidget,
}: UseDynamicLoadRemoteEntryProps): UseDynamicLoadRemoteEntryState => {
  const [failed, setFailed] = useState(false);

  const [remoteEntriesLoaded, setRemoteEntriesLoaded] = useAtom(
    remoteEntriesLoadedAtom,
  );

  useEffect((): (() => void) | undefined => {
    if (isEmpty(remoteEntry)) {
      return undefined;
    }

    const remoteEntryElement = document.getElementById(moduleName);

    if (remoteEntryElement && remoteEntriesLoaded.includes(moduleName)) {
      return undefined;
    }

    const prefix = isFederatedWidget ? 'widgets' : 'modules';

    const element = document.createElement('script');
    element.src = `./${prefix}/${moduleName}/static/${remoteEntry}`;
    element.type = 'text/javascript';
    element.id = moduleName;

    element.onload = (): void => {
      setRemoteEntriesLoaded((currentRemoteEntries) => [
        ...currentRemoteEntries,
        moduleName,
      ]);
    };

    element.onerror = (): void => {
      setFailed(true);
    };

    document.head.appendChild(element);

    return (): void => {
      document.head.removeChild(element);
    };
  }, []);

  return {
    failed,
    ready: remoteEntriesLoaded.includes(moduleName),
  };
};

interface LoadComponentProps {
  component: string;
  isFederatedModule?: boolean;
  memoProps: Array<unknown>;
  moduleFederationName: string;
  name: string;
}

const LoadComponent = ({
  name,
  moduleFederationName,
  component,
  isFederatedModule,
  memoProps,
  ...props
}: LoadComponentProps): JSX.Element => {
  const Component = useMemo(
    () => lazy(loadComponent({ component, moduleFederationName })),
    [moduleFederationName],
  );

  return useMemoComponent({
    Component: (
      <Suspense
        fallback={isFederatedModule ? <MenuSkeleton /> : <PageSkeleton />}
      >
        <Component {...props} />
      </Suspense>
    ),
    memoProps: [name, component, isFederatedModule, memoProps],
  });
};

interface RemoteProps extends LoadComponentProps {
  isFederatedWidget?: boolean;
  memoProps: Array<unknown>;
  moduleName: string;
  remoteEntry: string;
}

export const Remote = ({
  component,
  remoteEntry,
  moduleName,
  moduleFederationName,
  isFederatedModule,
  isFederatedWidget,
  memoProps,
  ...props
}: RemoteProps): JSX.Element => {
  const { ready, failed } = useDynamicLoadRemoteEntry({
    isFederatedWidget,
    moduleName,
    remoteEntry,
  });

  if (!ready) {
    return isFederatedModule ? <MenuSkeleton /> : <PageSkeleton />;
  }

  if (failed) {
    return <NotFoundPage />;
  }

  return (
    <LoadComponent
      {...props}
      component={component}
      isFederatedModule={isFederatedModule}
      memoProps={memoProps}
      moduleFederationName={moduleFederationName}
      name={moduleName}
    />
  );
};
