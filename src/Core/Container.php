<?php

namespace Kurama\Core;

use ReflectionClass;
use ReflectionException;
use Exception;

/**
 * Dependency Injection Container
 * 
 * This container manages dependencies and automatically resolves class dependencies
 * using reflection. It supports both singleton and transient lifetimes.
 */
class Container
{
    /**
     * Binding storage for services
     * @var array
     */
    private array $bindings = [];
    
    /**
     * Singleton instances storage
     * @var array
     */
    private array $instances = [];
    
    /**
     * Bind a service to the container with transient lifetime
     * 
     * @param string $abstract The interface or class name
     * @param callable $concrete The factory function
     * @return void
     */
    public function bind(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared' => false
        ];
    }
    
    /**
     * Bind a service as singleton (single instance throughout application lifecycle)
     * 
     * @param string $abstract The interface or class name
     * @param callable $concrete The factory function
     * @return void
     */
    public function singleton(string $abstract, callable $concrete): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared' => true
        ];
    }
    
    /**
     * Resolve a service from the container
     * 
     * @param string $abstract The interface or class name to resolve
     * @return mixed The resolved instance
     * @throws Exception When service cannot be resolved
     */
    public function resolve(string $abstract)
    {
        // Return existing singleton instance
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        
        // Check if we have a binding
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract]['concrete'];
            $instance = $concrete($this);
            
            // Store singleton instances
            if ($this->bindings[$abstract]['shared']) {
                $this->instances[$abstract] = $instance;
            }
            
            return $instance;
        }
        
        // Try to auto-resolve using reflection
        return $this->autoResolve($abstract);
    }
    
    /**
     * Make a new instance of a class with optional parameters
     * 
     * @param string $abstract The class name to instantiate
     * @param array $parameters Optional parameters to inject
     * @return mixed The new instance
     */
    public function make(string $abstract, array $parameters = [])
    {
        return $this->autoResolve($abstract, $parameters);
    }
    
    /**
     * Check if a service is bound in the container
     * 
     * @param string $abstract The service name to check
     * @return bool
     */
    public function bound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
    
    /**
     * Get all bound services
     * 
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
    
    /**
     * Auto-resolve a class using reflection
     * 
     * @param string $class The class name to resolve
     * @param array $parameters Optional parameters
     * @return mixed The resolved instance
     * @throws Exception When class cannot be instantiated
     */
    private function autoResolve(string $class, array $parameters = [])
    {
        try {
            $reflection = new ReflectionClass($class);
            
            if (!$reflection->isInstantiable()) {
                throw new Exception("Class {$class} is not instantiable");
            }
            
            $constructor = $reflection->getConstructor();
            
            // No constructor dependencies
            if (!$constructor) {
                return $reflection->newInstance();
            }
            
            $dependencies = [];
            foreach ($constructor->getParameters() as $parameter) {
                $type = $parameter->getType();
                
                if ($type && !$type->isBuiltin()) {
                    // Resolve class dependency
                    $dependencies[] = $this->resolve($type->getName());
                } else {
                    // Handle primitive parameters
                    $name = $parameter->getName();
                    if (isset($parameters[$name])) {
                        $dependencies[] = $parameters[$name];
                    } elseif ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new Exception("Cannot resolve parameter {$name} for class {$class}");
                    }
                }
            }
            
            return $reflection->newInstanceArgs($dependencies);
            
        } catch (ReflectionException $e) {
            throw new Exception("Cannot resolve class {$class}: " . $e->getMessage());
        }
    }
}